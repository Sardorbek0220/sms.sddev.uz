<?php
namespace App\Pbx;

class Onlinepbx {

	private $baseURL = "https://api2.onlinepbx.ru/";
	private $domain;
	private $apiKey;
	private $tokenFile;
	private $usersFile;

	public function __construct($domain, $apiKey, $tokenFile, $usersCacheFile) {
		$this->domain = $domain;
		$this->apiKey = $apiKey;
		$this->tokenFile = $tokenFile;
		$this->usersFile = $usersCacheFile;
	}

	private function getNewToken() {
		$ch = curl_init("{$this->baseURL}{$this->domain}/auth.json");
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json"
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
			"auth_key" => $this->apiKey,
			"new" => "true",
		]));
		$response = curl_exec($ch);
		curl_close($ch);
		
		if (empty($response)) {
			info("auth failed: empty response"); exit;
		}

		$data = json_decode($response, true);
		if (!$data) {
			info("auth failed: json_decode failed"); exit;
		}

		if ($data["status"] == "0") {
			info("auth failed: {$response}"); exit;
		}

		$data = $data["data"];
		$key = $data["key"];
		$key_id = $data["key_id"];
		$token = "{$key_id}:${key}";
		return $token;
	}

	private function request($apiName, $postData = "") {

		$token = file_get_contents($this->tokenFile);
		if ($token == false) {
			$token = self::getNewToken();
			file_put_contents($this->tokenFile, $token);
		}
		
		$requestUrl = "{$this->baseURL}{$this->domain}/{$apiName}";
		while (true) {
			$ch = curl_init($requestUrl);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"x-pbx-authentication: {$token}",
				"content-type: application/x-www-form-urlencoded"
			]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			$response = curl_exec($ch);
			$error = curl_error($ch);
			curl_close($ch);

			if ($response == false) {
				info("request failed: curl error = {$error}"); exit;
			}		

			$data = json_decode($response, true);
			if ($data["status"] == "0") {
				if ($data["isNotAuth"]) {
					$token = self::getNewToken();
					file_put_contents($this->tokenFile, $token);
				}
				else {
					info("request failed: ".$data["errorCode"].", comment: ".$data["comment"]);
				}
			}
			else {
				return $data["data"];
			}

		}

	}

	private function getUserList() {

		$response = $this->request("/user/get.json");
		$userList = [];
		foreach($response as $user) {
			$userList[$user["num"]] = $user["name"];
		}
		return $userList;
	}

	public function getCall($uuid) {
		return $this->request("/mongo_history/search.json", "uuid={$uuid}");
	}

	public function getUserName($localNumber) {

		$contents = file_get_contents($this->usersFile);
		if ($contents) {
			$contents = json_decode($contents, true);
			if (is_null($contents) || !isset($contents["next_update_time"]) || !isset($contents["users"])) {
				$contents = false;
			}
			else if ($contents["next_update_time"] < time()) {
				$contents = false;
			}
			else {
				$contents = $contents["users"];
				
				// if user not found
				if (!isset($contents[$localNumber])) {
					$contents = false;
				}
			}
		}

		if ($contents == false) {
			$contents = $this->getUserList();
			file_put_contents($this->usersFile, json_encode([
				"next_update_time" => time() + 86400, // one day
				"users" => $contents
			], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		}
		return $contents[$localNumber];
	}

	public function getQueueName($queueNum) {
		if ($queueNum == 5000) {
			return "Нерабочее_время";
		}
		else if ($queueNum == 5200) {
			return "Отдел_продаж";
		}
		else if ($queueNum == 5201) {
			return "Тех_отдел";
		}
		else if ($queueNum == 6100) {
			return "Умный_перевод";
		}
		else {
			return "{$queueNum}";
		}
	}

	public function getNumberTitle($num) {
		
		$num = intval($num);
		if ($num < 5000) {
			return $this->getUserName($num);
		}
		else {
			return $this->getQueueName($num);
		}
	}

}

?>