<?php

namespace App\Http\Controllers;

use App\Call;
use App\Operator;
use App\Client;

class AmocrmController extends Controller
{
    public function mainProcess()
    {
		try {
			
			$wm_string = iconv("windows-1251", "UTF-8", file_get_contents('php://input'));
			parse_str(urldecode($wm_string), $contents);

			$existCall = Call::where('uuid', $contents['uuid'])->first();
			if (empty($existCall) && $contents['gateway'] == '712075995') {

				if ($contents['direction'] === 'outbound') {
					$clientTel = $contents['callee'];
					$operTel = $contents['caller'];
				}else{
					$clientTel = $contents['caller'];
					$operTel = $contents['callee'];
				}

				$client = Client::where('telephone', $clientTel)->first();
				if (empty($client)) {

					// $client_data = $this->getClientInfo($contents['direction'] == 'outbound' ? $contents['callee'] : $contents['caller']);

					$company_name = '';
					$server_name = '';
					// if (!empty($client_data['companies'])) {
					// 	foreach ($client_data['companies'] as $company) {
					// 		$company_name .= ($company['name'] == 'undefined' ? '' : $company['name'].", ");
					// 		$server_name .= (is_null($company['server']) ? '' : $company['server'].", ");
					// 	}
					// }
					Client::create([
						'name' => $client_data['name'] ?? '',
						'telephone' => $clientTel,
						'company' => $company_name,
						'server' => $server_name
					]);
					
				}

				$operator = Operator::where('phone', $operTel)->first();

				if (empty($operator)) {
					$operator = Operator::create([
						'name' => 'Operator_name',
						'phone' => $operTel,
						'active' => 'Y'
					]);
				}

				$call = Call::create([
					'client_telephone' => $clientTel,
					'operator_id' => $operator['id'],
					'pbx_audio_url' => $contents['download_url'],
					'telegram_audio_url' => '',
					'event' => $contents['event'],
					'direction' => $contents['direction'],
					'call_duration' => $contents['call_duration'],
					'dialog_duration' => $contents['dialog_duration'],
					'uuid' => $contents['uuid'],
					'gateway' => $contents['gateway'],
					'date' => $contents['date']
				]);

				dd($call);
			}

		} catch (\Throwable $th) {
			dd($th);
		}
		
    }
}
