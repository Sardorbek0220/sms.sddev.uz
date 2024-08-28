<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WebSocketClient;

class WebSocketConnect extends Command
{
    protected $signature = 'websocket:connect';
    protected $description = 'Connect to the WebSocket server';

    protected $client;

    public function __construct(WebSocketClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function handle()
    {
        $this->client->connect();
    }
}