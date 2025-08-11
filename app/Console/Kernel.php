<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Hash;
use App\Call;
use App\All_call;
use AshAllenDesign\ShortURL\Classes\Builder;
use App\Operator_time;

class Kernel extends ConsoleKernel
{
    private $authKeyForAuth = "OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM";
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            
            $date1 = substr(date("Y-m-d H:i:s", (time() - 60 * 11)).gettimeofday()["dsttime"], 0, -1);
		    $date2 = substr(date("Y-m-d H:i:s", (time() - 60 * 10)).gettimeofday()["dsttime"], 0, -1);

            $date3 = substr(date("Y-m-d H:i:s", (time() - 60 * 240)).gettimeofday()["dsttime"], 0, -1);
            $date4 = substr(date("Y-m-d H:i:s", (time() - 60 * 0)).gettimeofday()["dsttime"], 0, -1);
            
            $calls = Call::where('event', 'call_end')->whereBetween('created_at', [$date1, $date2])->get();

            $sentCalls = Call::where('event', 'call_end')->where('sent_sms', 1)->whereBetween('updated_at', [$date3, $date4])->get();
            $sentPhones = [];
            foreach ($sentCalls as $sent) {
                $sentPhones[] = $sent['client_telephone'];
            }

            $messages = [];
            if (!empty($calls)) {
                // $phones = ['945535570', '998902226777', '902226777', '998935279065', '935279065'];
                foreach ($calls as $call) {
                    // if (!in_array($call['client_telephone'], $phones)) continue;
                    
                    if ($call['sent_sms'] === 1 || $call['dialog_duration'] < 30) continue;
                    if (in_array($call['client_telephone'], $sentPhones)) continue;
                    
                    $updCall = Call::find($call['id']);
                    $updCall->update(['sent_sms' => true]);

                    $hash = Hash::make($call['id']);
                    $hash = str_replace ('/', 'withoutslashes', $hash);

                    $builder = new Builder();
                    $shortURLObject = $builder->destinationUrl("https://sms.salesdoc.uz/feedback/".$call['id']."___".$hash)->make();
                    $shortURL = $shortURLObject->default_short_url;

					if($call['gateway'] == '712075995'){
						$mess = "Sales Doctor kompaniyasi sizning 712075995 nomer orqali so'nggi murojaatingizni baholashingizni so'raydi. ".$shortURL."";
					}elseif($call['gateway'] == '781138585'){
						$mess = "Ibox kompaniyasi sizning 781138585 nomer orqali so'nggi murojaatingizni baholashingizni so'raydi. ".$shortURL."";
					}else{
						$mess = "iDokon kompaniyasi sizning 781136022 nomer orqali so'nggi murojaatingizni baholashingizni so'raydi. ".$shortURL."";
					}

                    array_push($messages, [
                        'phone' => $call['client_telephone'],
                        'txt' => $mess
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
                    info($error_msg);
                }
            }
        })->everyMinute();

        $schedule->call(function () {
            self::getMonitoringCalls();
        })->everyMinute();

		$schedule->call(function () {
            $deleted = Operator_time::where('created_at', '<', date('Y-m-d'))->where('unregister', 0)->delete();
			info("deleted times: ".$deleted);
        })->dailyAt('10:20');
    }

	public function getMonitoringCalls(): void
	{
		$auth = json_decode(file_get_contents("/var/www/sms.sddev.uz/public/configs/auth.txt"));
		if (empty($auth->key) || empty($auth->key_id)) {
			$this->auth();
			$this->getMonitoringCalls();
		}

		$timeStamp = strtotime(date('Y-m-d H:i:s'));

		$data = [
			'start_stamp_from' => $timeStamp,
			'start_stamp_to' => $timeStamp
		];
		$url = 'https://api2.onlinepbx.ru/pbx12127.onpbx.ru/mongo_history/search.json';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["x-pbx-authentication: ".$auth->key_id.":".$auth->key]);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		$res = json_decode(curl_exec($ch));

		if (curl_errno($ch)) {
			curl_close($ch);
			info(curl_error($ch));
			exit;
		}
		curl_close($ch);

		if ($res->status == '0') {
			$this->auth();
			$this->getMonitoringCalls();
		}

		if ($res->status == '1') {
			$insert_rows = [];
			$uuids = [];
			foreach ($res->data as $datum) {
				$uuids[] = $datum->uuid;
			}
			
			$existCalls = All_call::whereIn("uuid", $uuids)->cursor();
			
			$existUuids = [];
			foreach ($existCalls as $call) {
				$existUuids[] = $call->uuid;
			}

			foreach ($res->data as $call) {
				if (!in_array($call->uuid, $existUuids)) {
					if ($call->accountcode == 'inbound' && $call->duration <= 5) {
						continue;
					}
					$insert_rows[] = [
						'uuid' => $call->uuid,
						'caller_id_name' => $call->caller_id_name,
						'caller_id_number' => $call->caller_id_number,
						'destination_number' => $call->destination_number,
						'start_stamp' => $call->start_stamp,
						'end_stamp' => $call->end_stamp,
						'duration' => $call->duration,
						'user_talk_time' => $call->user_talk_time,
						'accountcode' => $call->accountcode,
						'gateway' => $call->gateway,
					];
				}
			}

			All_call::insert($insert_rows);
		}
	}

    private function auth(): void
	{
		$data = [
			'auth_key' => $this->authKeyForAuth
		];
		$url = 'https://api2.onlinepbx.ru/pbx12127.onpbx.ru/auth.json';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		$res = json_decode(curl_exec($ch));

		if (curl_errno($ch)) {
			curl_close($ch);
			info(curl_error($ch));
			exit;
		}
		curl_close($ch);

		if ($res->status == '1') {
			file_put_contents("/var/www/sms.sddev.uz/public/configs/auth.txt", json_encode($res->data));
		}
	}

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
