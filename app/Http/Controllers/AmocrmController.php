<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Call;
use App\Operator;
use App\Client;
use AshAllenDesign\ShortURL\Classes\Builder;

class AmocrmController extends Controller
{
    private $subdomain = "sdteam";
	private $clientID = "70148299-1c63-4e85-bd9d-f0fe8f955cc3";
	private $clientSecret = "iCKJQoMH9asw2PZzyhOvytY01u2VgBYU6sIrZFhChMTVs5a9yPkYbSMZCHRyAe4Z";
	private $authCode = "def50200b8023797cd4e20619c7f471b9a4152690fd849cc5e7591c7461dd500d206fe2b5e8ba9d8197cd817973c8d55d44398c4b2362a696c8e0a88a2b27476d7d08933d2977ea5bbb91ce4ab553943cb51abe1dd1cee7da3e08d7a94c174318e866e995e1dcd4afb1373f524449154a6cc03e17f32499f9521fa5058a9a7d6139598dc8f0a19196cf49a3939df03c798d22aee67256fe68b0ff37b639b73b6a8d834810d8c46e3351e2e6b5d7c0411685c43d83d08040c1a84e2147329df539dd75ffcbd8cbd211ebcfbfa69bac709d90835f74efd9f235eb96bfa035136afe5c31f6a6e906e5e9688ea0d915e5ad18468f402b66c399c4ed5d701cb53925daa11c4961e6a8ae54ffb965cb3c49f1bfdfd879eb3a309868034140aa6c54accf13cb8e98f5079f0e10bfa9f686de73e016a3fa8f3c698c6c77ec3c80bcbc12c820507a19825f2ce7dc394efdbf9899ce7fb5b5aa3ca9cc1d119e810c5d622982c3cc3546684ba71da05e0c81a7c0db06878eb4fec1331ec01f5c2a079691181a445112c54ab5caf277303c0a8ebfa72ad8949ef90581763854c9796057a68d7bbac677e260e0ca31229868b54c1a35c825a3101a6a64558456fa63eb910041fdd4211ec8bbbb5b182da27697abfc0ee07e75d4ef4b0bbd604b78b4409f5ba93966fd5588b4b6c30fc35397b50d058dd0cff80f176d6c3b3";
	private $redirectURI = "https://salesdoc.io/configs/amocrm.php";
	private $tokenFile = 'configs/amocrm_token.json';

    public function mainProcess()
    {
		try {
			$wm_string = iconv("windows-1251", "UTF-8", file_get_contents('php://input'));
			parse_str(urldecode($wm_string), $contents);

			$client = Client::where('telephone', $contents['caller'])->first();
			if (empty($client)) {

				// $client_data = $this->getClientInfo($contents['caller']);
				$client_data = [];

				$company_name = '';
				$server_name = '';
				if (!empty($client_data['companies'])) {
					foreach ($client_data['companies'] as $company) {
						$company_name .= ($company['name'] == 'undefined' ? '' : $company['name'].", ");
						$server_name .= (is_null($company['server']) ? '' : $company['server'].", ");
					}
				}
				Client::create([
					'name' => $client_data['name'] ?? 'Без имени',
					'telephone' => $contents['caller'],
					'company' => $company_name,
					'server' => $server_name
				]);
			}

			$operator = Operator::where('phone', $contents['callee'])->first();

			if (!empty($operator)) {
				$call = Call::create([
					'client_telephone' => $contents['caller'],
					'operator_id' => $operator['id'],
					'pbx_audio_url' => $contents['download_url'],
					'telegram_audio_url' => '',
					'direction' => $contents['direction'],
					'dialog_duration' => $contents['dialog_duration'],
					'uuid' => $contents['uuid'],
					'gateway' => $contents['gateway'],
					'date' => $contents['date']
				]);
			}else{
				$call = [];
			}

			dd($call);

		} catch (\Throwable $th) {
			dd($th);
		}
		
    }

	private function saveToken($token) {
		$contents = [
			"refresh_token" => $token["refresh_token"],
			"access_token" => $token["access_token"],
			"expires_at" => $token["expires_at"] + time() - 20
		];
		file_put_contents($this->tokenFile, json_encode($contents, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	}

	private function getAccessToken($renew) {

		// read cached token
		$contents = file_get_contents($this->tokenFile);
		$contents = json_decode($contents, true);

		if (empty($contents)) {
			throw new ErrorException("{$this->tokenFile} file not found");
		}

		if (empty($contents["refresh_token"])) {
			throw new ErrorException("refresh token not found");
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
				dd("getAccessToken: status = {$status}\r\n{$response}");
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
				return json_decode($response ? $response : [], true);
			}
			else if (($code == 401 || $code == 403) && (--$maxAttempt)> 0) {
				$accessToken = $this->getAccessToken(true);
			}
			else {
				throw new ErrorException("GET {$requestUrl}: status code = ".$code);
			}
		}
		
	}

	public function getClientInfo($phoneNumber) {
		
		$contacts = $this->get("/api/v4/contacts", ["query" => $phoneNumber])["_embedded"]["contacts"];
		
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

	public function test(){
		$date1 = date("Y-m-d h:i:s", (time() - 60 * 21));
		$date2 = date("Y-m-d h:i:s", (time() - 60 * 0));
		$calls = Call::whereBetween('created_at', [$date1, $date2])->get();
		$messages = [];
		if (!empty($calls)) {
			foreach ($calls as $call) {
				
				if ($call['sent_sms'] === 1) continue;
				
				$updCall = Call::find($call['id']);
				$updCall->update(['sent_sms' => true]);

				$hash = Hash::make($call['id']);
				$hash = str_replace ('/', 'withoutslashes', $hash);

				$builder = new Builder();
				$shortURLObject = $builder->destinationUrl("https://sms.sddev.uz/feedback/".$call['id']."___".$hash)->make();
				$shortURL = $shortURLObject->default_short_url;

				array_push($messages, [
					'phone' => $call['client_telephone'],
					'txt' => "Hurmatli mijoz taklif hamda shikoyatlaringizni ushbu havolaga ".$shortURL." kirib qoldirishingiz mumkin."
				]);
			}
		}

		if (!empty($messages)) {

			$data = [
				'messages' => $messages
			];
			$url = 'https://billing.salesdoc.io/api/sms/sendingForward';
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, []);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			$res = curl_exec($ch);
	
			if (curl_errno($ch)) {
				$error_msg = curl_error($ch);
			}
			curl_close($ch);
	
			if (isset($error_msg)) {
				dd($error_msg);
			}
			dd($res);
		}
		
	}
}
