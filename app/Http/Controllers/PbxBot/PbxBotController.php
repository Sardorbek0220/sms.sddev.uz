<?php

namespace App\Http\Controllers\PbxBot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use App\Unknown_client;
use App\Pbx\Text;
use App\Pbx\Amocrm;
use App\Pbx\Onlinepbx;
use App\Pbx\Amo;
use App\Pbx\Pbx;

class PbxBotController extends Controller
{
    const TG_USER_CHANNEL = -1001467861516;
    const TG_MISSED_CALLS_CHANNEL = -1001964582608;
    const TG_USER_ME = 39672912;
    const BOT_URL = "https://api.telegram.org/bot5705052290:AAF5VkxlbjnEKzovZQW23mppKaOQIRc6sSQ/";

    public function sendTextMessage($chat_id, $text, $entities = [], $queryStatus = false) {
        $ch = curl_init(self::BOT_URL."sendMessage");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        $request = [
            "chat_id" => $chat_id,
            "text" => $text,
            "entities" => $entities,
        ];

        if ($queryStatus) {
            $request['reply_markup'] = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Проблема решена',
                            'callback_data' => 'problem_solved'
                        ],
                    ],
                    [
                        [
                            'text' => 'Проблема не решена',
                            'callback_data' => 'problem_not_solved'
                        ],
                    ],
                    [
                        [
                            'text' => 'Звонок перенаправлен',
                            'callback_data' => 'call_forwarded'
                        ],
                    ]
                ]
            ];
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        $response = curl_exec($ch);
        curl_close($ch);

        header('content-type: application/json');
        echo $response;
    }

    public function sendAudioMessage($chat_id, $caption, $caption_entities = [], $url, $queryStatus = false) {
        $ch = curl_init(self::BOT_URL."sendAudio");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        $request = [
            "chat_id" => $chat_id,
            "caption" => $caption,
            "caption_entities" => $caption_entities,
            "audio" => $url
        ];
        
        if ($queryStatus) {
            $request['reply_markup'] = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Проблема решена',
                            'callback_data' => 'problem_solved'
                        ],
                    ],
                    [
                        [
                            'text' => 'Проблема не решена',
                            'callback_data' => 'problem_not_solved'
                        ],
                    ],
                    [
                        [
                            'text' => 'Звонок перенаправлен',
                            'callback_data' => 'call_forwarded'
                        ],
                    ]
                ]
            ];
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_exec($ch);
        curl_close($ch);
    }

    public function getCallSummary() {

        $amo = new Amocrm(Amo::subdomain, Amo::clientID, Amo::clientSecret, Amo::authCode, Amo::redirectURI, Amo::tokenFile);
        $pbx = new Onlinepbx(Pbx::domain, Pbx::apiKey, Pbx::tokenFile, Pbx::usersCacheFile);

        $text = new Text();
        
        $client;
        $operator;
        if ($_POST["direction"] == "outbound") {
            $text->appendEntity("Исходящий звонок", "bold")->endl()->endl();
            $client = $_POST["callee"];
            $operator = $_POST["caller"];
        }
        else if ($_POST["direction"] == "inbound") {

            if ($_POST["event"] == "call_missed") {
                $text->appendEntity("Пропущенный входящий звонок", "bold")->endl()->endl();
            }
            else {
                $text->appendEntity("Входящий звонок", "bold")->endl()->endl();
            }
            $client = $_POST["caller"];
            $operator = $_POST["callee"];
        }
        else {
            return $text;
        }

        // add client phone number
        $text->appendEntity("Клиент: ", "bold")->appendEntity($client, "phone_number")->endl();


        // add client details 
        try {
            $info = $amo->getClientInfo($client);
            if ($info) {
                $text->appendEntity("    Имя: ", "bold")->appendText($info["name"])->endl();
                
                if (count($info["companies"]) == 0) {
                    $text->appendEntity("    Компания: ", "bold")->appendText("Не указана")->endl();
                    $text->appendEntity("    Сервер: ", "bold")->appendText("Не указан")->endl();
                    Unknown_client::create([
                        'phone' => $client,
                        'direction' => $_POST["direction"],
                        'operator' => $operator,
                        'event' => $_POST["event"]
                    ]);
                }
                else {
                    foreach($info["companies"] as $company) {
                        $companyName = $company["name"];
                        $serverName = $company["server"];
                        
                        if (empty($companyName)) {
                            $text->appendEntity("    Компания: ", "bold")->appendText("Не указана")->endl();
                        }
                        else {
                            $text->appendEntity("    Компания: ", "bold")->appendEntity($companyName, "hashtag")->endl();
                        }

                        if (empty($serverName)) {
                            $text->appendEntity("    Сервер: ", "bold")->appendText("Не указан")->endl();
                        }
                        else {
                            $text->appendEntity("    Сервер: ", "bold")->appendEntity("#".$serverName, "hashtag")->endl();
                        }

                        if (empty($serverName) || empty($info["name"])) {
                            Unknown_client::create([
                                'phone' => $client,
                                'direction' => $_POST["direction"],
                                'operator' => $operator,
                                'event' => $_POST["event"]
                            ]);
                        }
                    }
                }
            }
            else {
                $text->appendEntity("    Имя: ", "bold")->appendText("Не указана")->endl();
                $text->appendEntity("    Сервер: ", "bold")->appendText("Не указан")->endl();
                $text->appendEntity("    Компания: ", "bold")->appendText("Не указана")->endl();
                Unknown_client::create([
                    'phone' => $client,
                    'direction' => $_POST["direction"],
                    'operator' => $operator,
                    'event' => $_POST["event"]
                ]);
            }
        }
        catch(Exception $e) {
            sendTextMessage(self::TG_USER_ME, $e->getMessage());
            $text->appendEntity("    Имя: ", "bold")->appendText("Не указана")->endl();
            $text->appendEntity("    Сервер: ", "bold")->appendText("Не указан")->endl();
            $text->appendEntity("    Компания: ", "bold")->appendText("Не указана")->endl();
            Unknown_client::create([
                'phone' => $client,
                'direction' => $_POST["direction"],
                'operator' => $operator,
                'event' => $_POST["event"]
            ]);
        }
        $text->endl();

        // add operator name
        if (intval($operator) < 5000) {
            $operatorName = $pbx->getNumberTitle($operator);
            $operatorName = str_replace(' ', '_', $operatorName);
            $text->appendEntity("Оператор: ", "bold")->appendEntity("#".$operatorName, "hashtag")->endl()->endl();
        }
        else {
            $text->appendEntity("Оператор: ", "bold")->appendEntity("#".$pbx->getNumberTitle($operator), "hashtag")->endl()->endl();
        }
        

        // add gateway
        $text->appendEntity("Номер: ", "bold")->appendText($_POST["gateway"])->endl();
        
        // add date
        $text->appendEntity("Дата: ", "bold")->appendText(date("Y-m-d H:i:s", $_POST["date"]))->endl();

        if ($_POST['event'] == 'call_missed') {
            $result = $pbx->getCall($_POST['uuid']);
            $text->appendEntity('Длительность: ', 'bold')->appendText('')->endl();
        }

        return $text;
    }

    public function send()
    {
        if ($_SERVER["HTTP_CONTENT_TYPE"] != "application/x-www-form-urlencoded") {
            return;
        }
    
        $event = $_POST["event"];
        
        if (isset($_POST["test"]) && $_POST["test"]) {
            if (!empty($_POST['text'])) {
                $this->sendTextMessage(self::TG_USER_ME, $_POST['text'], [], true);
            }
            else {
                $summary = $this->getCallSummary();
                if (isset($_POST["download_url"])) {
                    $summary = $this->getCallSummary();
                    $this->sendAudioMessage(self::TG_USER_ME, $summary->text, $summary->entities, $_POST["download_url"], true);
                }
                else {
                    $this->sendTextMessage(self::TG_USER_ME, $summary->text, $summary->entities, true);
                }
            }
        }
        else if ($event == "call_missed") {
            if ($_POST["direction"] == "inbound") {
                $summary = $this->getCallSummary();
                $this->sendTextMessage(self::TG_USER_CHANNEL, $summary->text, $summary->entities);
                $this->sendTextMessage(self::TG_MISSED_CALLS_CHANNEL, $summary->text, $summary->entities);
            } 
        }
        else if ($event == "call_end") {
    
            $summary = $this->getCallSummary();
            if (isset($_POST["download_url"]) && !empty($_POST["download_url"])) {
                $this->sendAudioMessage(self::TG_USER_CHANNEL, $summary->text, $summary->entities, $_POST["download_url"], true);
            }
            else {
                $this->sendTextMessage(self::TG_USER_CHANNEL, $summary->text, $summary->entities, true);
            }
    
        }
    }
}