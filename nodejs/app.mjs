import WebSocket from 'ws'
import * as https from 'https'

const ws = new WebSocket('wss://pbx12127.onpbx.ru:3342/?key=OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM')

ws.on('error', console.error)

ws.on('open', function() {
    console.log('connection open')

    ws.send(
        JSON.stringify({
            command: 'subscribe',
       	    reqId: '1',
       	    data: {
       	        'eventGroups': ['user_blf', 'user_registration']
       	    }   
        })
    )
})

ws.on('message', function(data) {
	const message = JSON.parse(data.toString())
	if (message.event !== 'subscribed') {

		const postData = JSON.stringify(message)
		const request = https.request({
			hostname: 'sms.sddev.uz',
			path: '/pbx/event',
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Content-Length': Buffer.byteLength(postData),
				'X-Api-Key': 'MEGepAq0sBVd9gPZHyY1A07Oj7jmVC8'
			}
		}, function(res) {
			console.log('status: ', res.statusCode)
		})

		request.on('error', console.error)
		request.write(postData)
		request.end()
	}
})
