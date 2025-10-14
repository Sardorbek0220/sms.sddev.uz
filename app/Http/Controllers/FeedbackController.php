<?php

namespace App\Http\Controllers;

const TG_USER_CHANNEL = -1002077092594;
const BOT_URL = "https://api.telegram.org/bot6021707011:AAHXaS_dKTC5r2Jl-bwZueTs6Qb5zdXEZqk/";

const IBOX_TG_USER_CHANNEL = -4850688661;
const IBOX_BOT_URL = "https://api.telegram.org/bot8434467105:AAFOxMrVAUrnVdRXhy40CWUSGD7G7AXIYPI/";

const IDOKON_TG_USER_CHANNEL = -4897748669;
const IDOKON_BOT_URL= "https://api.telegram.org/bot8435561111:AAFZDQhJPAIGJ4iVUDEHmT9NSbMinuOtZAE/";

const STATUS = [
    "Жавоб олмадим",
    "Етарли жавоб олмадим",
    "Жавоб кутяпман",
    "Жавоб олдим",
    "Дастурда хатолик"
];

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Feedback;
use App\Text;
use App\Call;
use App\Operator;
use Response;

class FeedbackController extends Controller
{
    public function index($id)
    {
        try {
            //get rid of 'extra words'
            $id = str_replace('withoutslashes', '/', $id);

            $clientHash = explode("___", $id);

            $call_id = $clientHash[0];
            $hash = Hash::make($call_id);
            $call = Call::find($call_id);
            
            if (Hash::check($call_id, $clientHash[1])) {
                return view('feedback', compact('call_id', 'call'));
            }else{
                abort(404);
            }
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function store(Request $request){

        $existFeedback = Feedback::where('call_id', $request->call_id)->first();
        if (empty($existFeedback)) {
            $feedback = Feedback::create([
                'call_id' => $request->call_id,
                'complaint' => '',
                'solved' => $request->solved,
            ]);

            $message_id = 0;
            if ($feedback->id) {

                $infoCall = Call::find($request->call_id);
                $operator = Operator::find($infoCall->operator_id);

                $call_audio_url = $this->getUrl($infoCall->uuid);
    
                $text = new Text();
                $text->appendEntity("Operator: ", "bold")->appendText("#n_".$operator->phone." ".$operator->name)->endl();
                $text->appendEntity("Telefon nomer: ", "bold")->appendText($infoCall->client_telephone)->endl();
                $text->appendEntity("Baho: ", "bold")->appendText("#mark".$request->solved." ".STATUS[$request->solved])->endl();
                $text->appendEntity("Qo'ng'iroq vaqti: ", "bold")->appendText($infoCall->created_at)->endl();
                $text->appendEntity("ID: ", "bold")->appendText("#id_".$infoCall->id)->endl();
                if ($request->solved == '4' && $infoCall->gateway == '712075995') {
                    $text->appendEntity("", "bold")->appendText("@silence1508")->endl();
                }
                $text->endl();

                $ch = curl_init(($infoCall->gateway == '712075995' ? BOT_URL : ($infoCall->gateway == '781138585' ? IBOX_BOT_URL : IDOKON_BOT_URL))."sendAudio");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json"
                ]);

                $request = [
                    "chat_id" => ($infoCall->gateway == '712075995' ? TG_USER_CHANNEL : ($infoCall->gateway == '781138585' ? IBOX_TG_USER_CHANNEL : IDOKON_TG_USER_CHANNEL)),
                    "audio" => $call_audio_url,
                    "caption" => $text->text,
                    "caption_entities" => $text->entities,
                ];

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
                $response = curl_exec($ch);
                curl_close($ch);
                // info($response);

                $response = json_decode($response);
                if ($response->ok == true) {
                    $message_id = $response->result->message_id;
                }
            }

            return Response::json(["data" => $feedback, "message_id" => $message_id]);
        }else{
            return Response::json([]);
        }

    }

    public function afterStore(Request $request){
        
        $feedback = Feedback::where('call_id', $request->call_id)->first();
        if (!empty($feedback)) {
            $feedback->update([
                'complaint' => $request->complaint
            ]);
            if (!empty($request->complaint) && $request->message_id != 0) {
                $infoCall = Call::find($request->call_id);
                $operator = Operator::find($infoCall->operator_id);

                $text = new Text();
	
                $text->appendEntity("Operator: ", "bold")->appendText("#n_".$operator->phone." ".$operator->name)->endl();
                $text->appendEntity("Telefon nomer: ", "bold")->appendText($infoCall->client_telephone)->endl();
                $text->appendEntity("Baho: ", "bold")->appendText("#mark".$feedback->solved." ".STATUS[$feedback->solved])->endl();
                $text->appendEntity("Qo'ng'iroq vaqti: ", "bold")->appendText($infoCall->created_at)->endl();
                $text->appendEntity("Izoh: ", "bold")->appendText($feedback->complaint)->endl();
                $text->appendEntity("ID: ", "bold")->appendText("#id_".$infoCall->id)->endl();
                if ($request->solved == '4' && $infoCall->gateway == '712075995') {
                    $text->appendEntity("", "bold")->appendText("@silence1508")->endl();
                }
                $text->endl();

                $ch = curl_init(($infoCall->gateway == '712075995' ? BOT_URL : ($infoCall->gateway == '781138585' ? IBOX_BOT_URL : IDOKON_BOT_URL))."editMessageCaption");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json"
                ]);

                $request = [
                    "chat_id" => ($infoCall->gateway == '712075995' ? TG_USER_CHANNEL : ($infoCall->gateway == '781138585' ? IBOX_TG_USER_CHANNEL : IDOKON_TG_USER_CHANNEL)),
                    "message_id" => intval($request->message_id),
                    "caption" => $text->text,
                    "caption_entities" => $text->entities,
                ];

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
                $response = curl_exec($ch);
                curl_close($ch);
                // info($response);
            }
            return Response::json($feedback);
        }else{
            return Response::json([]);
        }
    }

    public function success(){
        return view('success');
    }

    public function all(Request $request){
        if ($request->from_date == null) {
            $from_date = date('Y-m-d');
        }else{
            $from_date = $request->from_date;
        }

        if ($request->to_date == null) {
            $to_date = date('Y-m-d');
        }else{
            $to_date = $request->to_date;
        } 
        $status = STATUS;

        $allFeedback = Feedback::
            whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
            ->orderByDesc('created_at')
            ->when(($request->type != "1111" && !empty($request->type)), function($query) use($request){
                return $query->where('solved', $request->type);
            })
            ->get();
        return view('admin.feedback', compact('allFeedback', 'from_date', 'to_date', 'status'));
    }

    public function getUrl($uuid){
        $postData = array(
            'auth_key' => "OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM"
        );

        $ch = curl_init("https://api2.onlinepbx.ru/pbx12127.onpbx.ru/auth.json");
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($postData)
        ));

        $response = curl_exec($ch);
        $response = json_decode($response);
        if ($response->status == 1) {
            $postData = array(
                'uuid' => $uuid,
                'download' => 1
            );

            $key = $response->data->key;
            $key_id = $response->data->key_id;

            $ch = curl_init("https://api2.onlinepbx.ru/pbx12127.onpbx.ru/mongo_history/search.json");
            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "x-pbx-authentication: $key_id:$key"
                ),
                CURLOPT_POSTFIELDS => json_encode($postData)
            ));

            $response = curl_exec($ch);
            $response = json_decode($response);
            if ($response->status == 1) {
                return $response->data;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
