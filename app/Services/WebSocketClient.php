<?php

namespace App\Services;

use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use React\Socket\Connector as ReactConnector;
use Illuminate\Support\Facades\Log;

/**
 * WebSocket bridge to OnlinePBX → feeds the live monitoring page.
 *
 * Run as a daemon (systemd unit phone-bridge.service):
 *   php artisan websocket:connect
 *
 * Responsibilities:
 *   - Subscribe to user_registration / user_blf / user_status event groups.
 *   - Forward every relevant event to PbxLiveStateService::ingest() so
 *     /admin/monitoring shows real-time operator state.
 *   - Track operator register/unregister times in `operator_times` table
 *     (preserves legacy attendance-report behaviour).
 *   - Emit a synthetic bridge_heartbeat every 10s so the monitoring page
 *     reports "bridge_connected = true".
 *   - Auto-reconnect indefinitely with exponential backoff (max 30s).
 */
class WebSocketClient
{
    private const WS_URL = 'wss://pbx12127.onpbx.ru:3342/?key=OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM';
    private const SUBSCRIBE_GROUPS = ['user_registration', 'user_blf', 'user_status'];
    private const HEARTBEAT_INTERVAL_SECONDS = 3;
    private const STALE_CONNECTION_SECONDS = 300; // force reconnect if no events for 5 min
    private const RECONNECT_INITIAL_BACKOFF = 1;
    private const RECONNECT_MAX_BACKOFF = 30;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    /** @var Connector */
    protected $connector;

    /** @var PbxLiveStateService */
    protected $liveState;

    /** @var int */
    protected $reconnectBackoff = self::RECONNECT_INITIAL_BACKOFF;

    /** @var \React\EventLoop\TimerInterface|null */
    protected $heartbeatTimer = null;

    /** @var int Unix timestamp of last real (non-heartbeat) event from OnlinePBX. */
    protected $lastRealEventAt = 0;

    /** @var \React\EventLoop\TimerInterface|null Watchdog timer */
    protected $stalenessTimer = null;

    /** @var object|null Currently open connection — used by watchdog to close. */
    protected $currentConn = null;

    public function __construct(PbxLiveStateService $liveState)
    {
        $this->loop = Factory::create();
        $reactConnector = new ReactConnector($this->loop);
        $this->connector = new Connector($this->loop, $reactConnector);
        $this->liveState = $liveState;
    }

    public function connect()
    {
        $this->log('connecting to ' . self::WS_URL);

        ($this->connector)(self::WS_URL)
            ->then(
                function ($conn) {
                    $this->onOpen($conn);
                },
                function ($e) {
                    $this->log('connect failed: ' . $e->getMessage(), 'error');
                    $this->scheduleReconnect();
                }
            );

        $this->loop->run();
    }

    protected function onOpen($conn): void
    {
        $this->reconnectBackoff = self::RECONNECT_INITIAL_BACKOFF;
        $this->lastRealEventAt = time();
        $this->currentConn = $conn;
        $this->log('connected, subscribing to ' . implode(',', self::SUBSCRIBE_GROUPS));

        $conn->send(json_encode([
            'command' => 'subscribe',
            'reqId'   => (string) time(),
            'data'    => ['eventGroups' => self::SUBSCRIBE_GROUPS],
        ]));

        // Synthetic bridge_connected immediately so monitoring flips to "alive"
        $this->safeIngest(['event' => 'bridge_connected', 'data' => []]);

        // Heartbeat keeps PbxLiveStateService::bridge_seen_at fresh.
        $this->heartbeatTimer = $this->loop->addPeriodicTimer(self::HEARTBEAT_INTERVAL_SECONDS, function () {
            $this->safeIngest(['event' => 'bridge_heartbeat', 'data' => []]);
        });

        // Watchdog: if no real events in STALE_CONNECTION_SECONDS, force reconnect.
        $this->stalenessTimer = $this->loop->addPeriodicTimer(30, function () {
            $age = time() - ($this->lastRealEventAt ?: time());
            if ($age > self::STALE_CONNECTION_SECONDS) {
                $this->log("no events for {$age}s — forcing reconnect", 'warning');
                $this->lastRealEventAt = time(); // prevent immediate re-trigger
                if ($this->currentConn) {
                    try { $this->currentConn->close(); } catch (\Throwable $e) {}
                }
            }
        });

        $conn->on('message', function ($msg) {
            $this->onMessage((string) $msg);
        });

        $conn->on('close', function ($code = null, $reason = null) {
            $this->log("connection closed (code={$code} reason={$reason})", 'warning');
            $this->stopHeartbeat();
            $this->scheduleReconnect();
        });

        $conn->on('error', function ($e) {
            $this->log('connection error: ' . $e->getMessage(), 'error');
        });
    }

    protected function onMessage(string $raw): void
    {
        $msg = json_decode($raw);
        if (!$msg || !isset($msg->event)) {
            return;
        }

        $event = (string) $msg->event;
        $data = isset($msg->data) ? (array) $msg->data : [];

        $this->lastRealEventAt = time();
        // Forward to live-state. PbxLiveStateService::ingest() also writes
        // operator_times via syncOperatorTime() — single source of truth.
        $this->safeIngest(['event' => $event, 'data' => $data]);
    }

    protected function safeIngest(array $payload): void
    {
        try {
            $this->liveState->ingest($payload);
        } catch (\Throwable $e) {
            $this->log('ingest failed: ' . $e->getMessage(), 'error');
        }
    }

    protected function stopHeartbeat(): void
    {
        if ($this->heartbeatTimer !== null) {
            $this->loop->cancelTimer($this->heartbeatTimer);
            $this->heartbeatTimer = null;
        }
        if ($this->stalenessTimer !== null) {
            $this->loop->cancelTimer($this->stalenessTimer);
            $this->stalenessTimer = null;
        }
        $this->currentConn = null;
    }

    protected function scheduleReconnect(): void
    {
        $delay = $this->reconnectBackoff;
        $this->reconnectBackoff = min(self::RECONNECT_MAX_BACKOFF, $this->reconnectBackoff * 2);
        $this->log("reconnecting in {$delay}s");
        $this->loop->addTimer($delay, function () {
            ($this->connector)(self::WS_URL)
                ->then(
                    function ($conn) { $this->onOpen($conn); },
                    function ($e) {
                        $this->log('reconnect failed: ' . $e->getMessage(), 'error');
                        $this->scheduleReconnect();
                    }
                );
        });
    }

    protected function log(string $msg, string $level = 'info'): void
    {
        $line = '[' . date('Y-m-d H:i:s') . "] [{$level}] phone-bridge: {$msg}";
        // Write to stdout so systemd journal captures it.
        echo $line . PHP_EOL;
        @file_put_contents(
            storage_path('logs/phone-bridge.log'),
            $line . PHP_EOL,
            FILE_APPEND
        );
    }
}
