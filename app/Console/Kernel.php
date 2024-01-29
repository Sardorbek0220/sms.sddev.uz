<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Hash;
use App\Call;
use AshAllenDesign\ShortURL\Classes\Builder;

class Kernel extends ConsoleKernel
{
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
            // $date1 = date("Y-m-d h:i:s", (time() - 60 * 5));
            // $date2 = date("Y-m-d h:i:s", (time() - 60 * 0));
            $date1 = substr(date("Y-m-d H:i:s", (time() - 60 * 2)).gettimeofday()["dsttime"], 0, -1);
		    $date2 = substr(date("Y-m-d H:i:s", (time() - 60 * 0)).gettimeofday()["dsttime"], 0, -1);
            $calls = Call::where('event', 'call_end')->whereBetween('created_at', [$date1, $date2])->get();
            $messages = [];
            if (!empty($calls)) {
                $phones = ['945535570', '998902226777', '902226777', '998935279065', '935279065'];
                foreach ($calls as $call) {
                    if (!in_array($call['client_telephone'], $phones)) continue;
                    if ($call['sent_sms'] === 1) continue;
                    
                    $updCall = Call::find($call['id']);
                    $updCall->update(['sent_sms' => true]);

                    $hash = Hash::make($call['id']);
                    $hash = str_replace ('/', 'withoutslashes', $hash);

                    $builder = new Builder();
                    $shortURLObject = $builder->destinationUrl("https://sms.salesdoc.uz/feedback/".$call['id']."___".$hash)->make();
                    $shortURL = $shortURLObject->default_short_url;

                    array_push($messages, [
                        'phone' => $call['client_telephone'],
                        'txt' => "Sales Doctor компанияси сизнинг 712079559 номер орқали сўнгги мурожаатингизга кўрсатилган хизматни баҳолашингизни сўрайди. Хавола: ".$shortURL.""
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
                    // dd($error_msg);
                }
                // dd($res);
            }
        })->everyMinute();

        // $schedule->call(function () {
        //     info('5');
        // })->everyFiveMinutes();
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
