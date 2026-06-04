<?php

namespace App\Http\Controllers;

use App\Call;
use App\Operator;
use App\Client;
use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Log;

class AmocrmController extends Controller
{
    public function mainProcess()
    {
		try {
			
			$wm_string = iconv("windows-1251", "UTF-8", file_get_contents('php://input'));
			parse_str(urldecode($wm_string), $contents);

			$existCall = Call::where('uuid', $contents['uuid'])->first();
			if (empty($existCall) && in_array($contents['gateway'], ['712075995', '781138585', '781136022'])) {

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
						'name'      => '',
						'telephone' => (string) $clientTel,
						'company'   => (string) $company_name,
						'server'    => (string) $server_name,
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

				$real_url = "";
				if (!empty($contents['uuid'])) {
					$feedbackController = new FeedbackController();
					$real_url = $feedbackController->getUrl($contents['uuid']);
				}

				// Force scalar casts: PBX webhook occasionally sends array values
				// for some fields, which crashes Eloquent string-column inserts.
				$str = function ($v) { return is_scalar($v) ? (string) $v : (is_array($v) ? implode(',', array_filter($v, 'is_scalar')) : ''); };
				$int = function ($v) { return is_numeric($v) ? (int) $v : 0; };

				$call = Call::create([
					'client_telephone'   => $str($clientTel),
					'operator_id'        => $int($operator['id']),
					'pbx_audio_url'      => $str($contents['download_url'] ?? ''),
					'telegram_audio_url' => $str($real_url),
					'event'              => $str($contents['event'] ?? ''),
					'direction'          => $str($contents['direction'] ?? ''),
					'call_duration'      => $int($contents['call_duration'] ?? 0),
					'dialog_duration'    => $int($contents['dialog_duration'] ?? 0),
					'uuid'               => $str($contents['uuid'] ?? ''),
					'gateway'            => $str($contents['gateway'] ?? ''),
					'date'               => $str($contents['date'] ?? ''),
				]);

				Log::info('AmocrmController.mainProcess: call stored', ['call_id' => $call->id ?? null]);
			}

		} catch (\Throwable $th) {
			Log::error('AmocrmController.mainProcess failed: ' . $th->getMessage(), ['exception' => get_class($th), 'file' => $th->getFile(), 'line' => $th->getLine()]);
		}
		
    }
}
