import WebSocket from 'ws'
import * as https from 'https'

const PBX_WS_URL = process.env.PBX_WS_URL || 'wss://pbx12127.onpbx.ru:3342/?key=OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM'
const PBX_EVENT_URL = process.env.PBX_EVENT_URL || 'https://phone.sddev.uz/pbx/event'
const PBX_EVENT_API_KEY = process.env.PBX_EVENT_API_KEY || 'MEGepAq0sBVd9gPZHyY1A07Oj7jmVC8'
const PBX_EVENT_GROUPS = (process.env.PBX_EVENT_GROUPS || 'user_blf,user_registration').split(',').map((item) => item.trim()).filter(Boolean)
const PBX_RECONNECT_MS = Number(process.env.PBX_RECONNECT_MS || 3000)
const PBX_HEARTBEAT_MS = Number(process.env.PBX_HEARTBEAT_MS || 15000)

let reconnectTimer = null
let heartbeatTimer = null
let ws = null

function buildTargetOptions() {
    const url = new URL(PBX_EVENT_URL)

    return {
        protocol: url.protocol,
        hostname: url.hostname,
        port: url.port || (url.protocol === 'https:' ? 443 : 80),
        path: url.pathname + url.search,
    }
}

function postEvent(payload) {
    return new Promise((resolve, reject) => {
        const postData = JSON.stringify(payload)
        const target = buildTargetOptions()
        const request = https.request({
            hostname: target.hostname,
            port: target.port,
            path: target.path,
            method: 'POST',
            timeout: 10000,
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(postData),
                'X-Api-Key': PBX_EVENT_API_KEY,
            }
        }, function(res) {
            const chunks = []
            res.on('data', chunk => chunks.push(chunk))
            res.on('end', () => {
                if (res.statusCode && res.statusCode >= 200 && res.statusCode < 300) {
                    resolve(Buffer.concat(chunks).toString())
                    return
                }

                reject(new Error('Bridge endpoint returned status ' + res.statusCode))
            })
        })

        request.on('timeout', () => {
            request.destroy(new Error('Bridge endpoint timeout'))
        })

        request.on('error', reject)
        request.write(postData)
        request.end()
    })
}

async function sendHeartbeat(state = 'connected') {
    try {
        await postEvent({
            event: 'bridge_heartbeat',
            data: {
                state,
                source: 'node-bridge',
                eventGroups: PBX_EVENT_GROUPS,
            }
        })
    } catch (error) {
        console.error('heartbeat failed', error.message)
    }
}

function clearTimers() {
    if (reconnectTimer) {
        clearTimeout(reconnectTimer)
        reconnectTimer = null
    }

    if (heartbeatTimer) {
        clearInterval(heartbeatTimer)
        heartbeatTimer = null
    }
}

function scheduleReconnect() {
    if (reconnectTimer) {
        return
    }

    reconnectTimer = setTimeout(() => {
        reconnectTimer = null
        connect()
    }, PBX_RECONNECT_MS)
}

function connect() {
    clearTimers()

    console.log('connecting to PBX websocket...')
    ws = new WebSocket(PBX_WS_URL)

    ws.on('open', async function() {
        console.log('PBX websocket connected')

        ws.send(JSON.stringify({
            command: 'subscribe',
            reqId: 'pbx-bridge',
            data: {
                eventGroups: PBX_EVENT_GROUPS
            }
        }))

        await sendHeartbeat('connected')
        heartbeatTimer = setInterval(() => {
            sendHeartbeat('alive')
        }, PBX_HEARTBEAT_MS)
    })

    ws.on('message', async function(data) {
        let message

        try {
            message = JSON.parse(data.toString())
        } catch (error) {
            console.error('invalid PBX payload', error.message)
            return
        }

        if (!message || message.event === 'subscribed') {
            return
        }

        try {
            await postEvent(message)
        } catch (error) {
            console.error('failed to forward PBX event', error.message)
        }
    })

    ws.on('error', function(error) {
        console.error('PBX websocket error', error.message)
    })

    ws.on('close', function(code) {
        console.warn('PBX websocket closed', code)
        clearTimers()
        scheduleReconnect()
    })
}

process.on('SIGINT', () => {
    clearTimers()
    if (ws) {
        ws.close()
    }
    process.exit(0)
})

process.on('SIGTERM', () => {
    clearTimers()
    if (ws) {
        ws.close()
    }
    process.exit(0)
})

process.on('uncaughtException', (error) => {
    console.error('uncaught exception', error)
    scheduleReconnect()
})

process.on('unhandledRejection', (error) => {
    console.error('unhandled rejection', error)
})

connect()
