<?php

namespace App\Services;

use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use React\Socket\Connector as ReactConnector;
use App\Operator_time;

class WebSocketClient
{
    protected $loop;

    public function __construct()
    {
        $this->loop = Factory::create();
    }

    public function connect()
    {
        $reactConnector = new ReactConnector($this->loop);

        $connector = new Connector($this->loop, $reactConnector);

        $connector('wss://pbx12127.onpbx.ru:3342/?key=OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM')
            ->then(function ($conn) {
                echo "Connected to WebSocket server\n";

                $message = json_encode([
                    "command" => "subscribe",
                    "reqId" => "123123",
                    "data" => [
                        "eventGroups" => ["user_registration"]
                    ]
                ]);

                $conn->send($message);

                $conn->on('message', function ($msg) {
                    $data = json_decode($msg);
                    if ($data->event == 'user_registration') {
                        info("Received: {$msg}\n");
                        $operator_time = Operator_time::create([
                            'uid' => $data->data->uid,
                            'status' => $data->data->state,
                            'ip' => $data->data->ip,
                            'port' => $data->data->port,
                            'timestamp' => $data->data->date,
                        ]);
                        $operator_time->save();
                        
                    }
                    
                });

                $conn->on('close', function () {
                    echo "Connection closed\n";
                });
            }, function ($e) {
                echo "Could not connect: {$e->getMessage()}\n";
            });

        $this->loop->run();
    }
}