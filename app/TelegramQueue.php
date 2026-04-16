<?php
namespace App;
    class TelegramQueue {
        public static function send($apiUrl, $method, $params) {
            $params['url'] = $apiUrl;
            $params['method'] = $method;
            $params['host'] = $_SERVER['HTTP_HOST'];
            $url = 'http://213.148.23.196:3000/addRequest';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }
    }
?>