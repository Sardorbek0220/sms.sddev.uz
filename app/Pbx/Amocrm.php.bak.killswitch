<?php
namespace App\Pbx;

class Amocrm {

	private $subdomain;
	private $clientID;
	private $clientSecret;
	private $authCode;
	private $redirectURI;
	private $tokenFile;

	function __construct($subdomain, $clientID, $clientSecret, $authCode, $redirectURI, $tokenFile) {
		$this->subdomain = $subdomain;
		$this->clientID = $clientID;
		$this->clientSecret = $clientSecret;
		$this->authCode = $authCode;
		$this->redirectURI = $redirectURI;
		$this->tokenFile = $tokenFile;
	}

	public function exchangeAuthorizationCode() {

		$ch = curl_init("https://{$this->subdomain}.amocrm.ru/oauth2/access_token");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json"
		]);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
			"client_id" => $this->clientID,
			"client_secret" => $this->clientSecret,
			"grant_type" => "authorization_code",
			"code" => $this->authCode,
			"redirect_uri" => $this->redirectURI
		]));

		$response = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		if ($status == 200) {
			$this->saveToken(json_decode($response, true));
			echo $response;
		}
		else {
			info("exchangeAuthorizationCode:: status code = {$status}\r\n{$response}");
		}
	}

	private function saveToken($token) {
		$contents = [
			"refresh_token" => $token["refresh_token"],
			"access_token" => $token["access_token"],
			"expires_at" => $token["expires_in"] + time() - 20
		];
		file_put_contents($this->tokenFile, json_encode($contents, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	}

	private function getAccessToken($renew) {

		// read cached token
		$contents = file_get_contents($this->tokenFile);
		$contents = json_decode($contents, true);

		if (empty($contents)) {
			info("{$this->tokenFile} file not found"); exit;
		}

		if (empty($contents["refresh_token"])) {
			info("refresh token not found"); exit;
		}

		if (empty($contents["access_token"]) || empty($contents["expires_at"]) || $contents["expires_at"] < time()) {
			$renew = true;
		}

		if ($renew) {

			$ch = curl_init("https://{$this->subdomain}.amocrm.ru/oauth2/access_token");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Content-Type: application/json"
			]);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
				'client_id' => $this->clientID,
				'client_secret' => $this->clientSecret,
				'grant_type' => 'refresh_token',
				'refresh_token' => $contents["refresh_token"],
				'redirect_uri' => $this->redirectURI
			]));

			$response = curl_exec($ch);
			$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = curl_error($ch);
			curl_close($ch);

			if ($status == 200) {
				$contents = json_decode($response, true);
				$this->saveToken($contents);
			}
			else {
				info("getAccessToken: status = {$status}\r\n{$response}");
			}
			
		}

		return $contents["access_token"];
	}

	private function get($apiName, $queryParams = []) {

		$accessToken = $this->getAccessToken(false);

		$maxAttempt = 2; // prevents infinite loop
		while (true) {
			$requestUrl = "https://{$this->subdomain}.amocrm.ru{$apiName}?".http_build_query($queryParams);
			$ch = curl_init($requestUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Authorization: Bearer {$accessToken}"
			]);
			$response = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = curl_error($ch);
			curl_close($ch);

			if ($code >= 200 && $code < 300) {
				// info("response: ".$response);
				return empty($response) ? [] : json_decode($response, true);
			}
			else if (($code == 401 || $code == 403) && (--$maxAttempt)> 0) {
				$accessToken = $this->getAccessToken(true);
			}
			else {
				info("GET {$requestUrl}: status code = ".$code);
				exit;
			}
		}
		
	}

	private function post($apiName, $body) {

		$accessToken = $this->getAccessToken(false);

		$maxAttempt = 2; // prevents infinite loop
		while (true) {
			$requestUrl = "https://{$this->subdomain}.amocrm.ru{$apiName}";
			$ch = curl_init($requestUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
				"Authorization: Bearer {$accessToken}"
			]);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

			$response = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = curl_error($ch);
			curl_close($ch);

			if ($code >= 200 && $code < 300) {
				return json_decode($response ? $response : [], true);
			}
			else if (($code == 401 || $code == 403) && (--$maxAttempt)> 0) {
				$accessToken = $this->getAccessToken(true);
			}
			else {
				info("POST {$requestUrl} failed. status = {$code}, response = {$response}");
			}
		}
		
	}
	
	public function getClientInfo($phoneNumber) {

		$infoClient = $this->get("/api/v4/contacts", ["query" => $phoneNumber]);
		if (empty($infoClient)) {
			return false;
		}
		
		$contacts = $infoClient["_embedded"]["contacts"];
		
		if (count($contacts) == 0) {
			return false;
		}

		if (count($contacts) > 1) {
			// We need contacts who has company attached
			$filteredContacts = array_values(array_filter($contacts, function($contact) {
				return count($contact["_embedded"]["companies"]);
			}));

			if (count($filteredContacts) > 0) {
				$contacts = $filteredContacts;
			}
		}

		// Convert data to make suitable for processing
		$contacts = array_map(function($contact) {

			$companies = $contact["_embedded"]["companies"];
			$companies = array_map(function($company){
				$company =  self::get("/api/v4/companies/".$company["id"]);
				$serverName = null;
				if (isset($company["custom_fields_values"])) {
					foreach($company["custom_fields_values"] as $field) {
						if ($field["field_id"] == 1050917) {
							if (count($field["values"]) > 0) {
								$serverName = $field["values"][0]["value"];
							}
							break;
						}
					}
				}
				return [
					"name" => $company["name"],
					"server" => $serverName
				];
			}, $companies);

			return [
				"name" => $contact["name"],
				"companies" => $companies
			];

		}, $contacts);
		return $contacts[0];
	}

	public function createLead($name, $phone, $email, $company, $pipelineID, $formName, $formPage, $referer, $ip) {
		return $this->post('/api/v4/leads/complex', [
			[
				"name" => $phone,
				"pipeline_id" => (int) $pipelineID,
				"_embedded" => [
					"metadata" => [
						"category" => "forms",
						"form_id" => 1,
						"form_name" => $formName,
						"form_page" => $formPage,
						"form_sent_at" => strtotime(date("Y-m-d H:i:s")),
						"ip" => $ip,
						"referer" => $referer
					],
					"contacts" => [
						[
							"name" => $name,
							"custom_fields_values" => [
								[
									"field_code" => "EMAIL",
									"values" => [
										[
											"enum_code" => "WORK",
											"value" => $email
										]
									]
								],
								[
									"field_code" => "PHONE",
									"values" => [
										[
											"enum_code" => "WORK",
											"value" => $phone
										]
									]
								]
							]
						]
					],
					"companies" => [
						[
							"name" => $company
						]
					]
				],
			]
		]);
	}

	public function createNote($leadID, $note) {
		return $this->post('/api/v4/leads/notes', [
			[
				'entity_id' => $leadID,
				'note_type' => 'common',
				'params' => [
					'text' => $note
				]
			]
		]);
	}

}

?>