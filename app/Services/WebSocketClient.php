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
                    // info("Received: {$msg}\n");
                    if ($data->event == 'user_registration') {
                        // info("Received: {$msg}\n");
                        // if (in_array($data->data->ip, ['84.54.117.196', '178.218.201.191'])) {
                            if ($data->data->state == 'register') {
                                $exist = Operator_time::where('uid', $data->data->uid)->where('port', $data->data->port)->where('ip', $data->data->ip)->where('unregister', 0)->where('created_at', '>', date('Y-m-d'))->first();
                                if ($exist == null) {
                                    $operator_time = Operator_time::create([
                                        'uid' => $data->data->uid,
                                        'register' => 1,
                                        'unregister' => 0,
                                        'ip' => $data->data->ip,
                                        'port' => $data->data->port,
                                        'timestamp_reg' => $data->data->date,
                                    ]);
                                    $operator_time->save();
                                }
                            }else if($data->data->state == 'unregister'){
                                $operator_time = Operator_time::where('uid', $data->data->uid)->where('port', $data->data->port)->where('ip', $data->data->ip)->orderBy('timestamp_reg', 'desc')->first();
                                if($operator_time != null){
                                    $operator_time->unregister = 1;
                                    $operator_time->timestamp_unreg = $data->data->date;
                                    $operator_time->save();
                                }
                                
                            }
                        // }
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