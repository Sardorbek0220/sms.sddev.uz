<?php

namespace App\Http\Controllers;

const TG_USER_CHANNEL = -1002077092594;
const BOT_URL = "https://api.telegram.org/bot6021707011:AAHXaS_dKTC5r2Jl-bwZueTs6Qb5zdXEZqk/";
const STATUS = [
    "Жавоб олмадим",
    "Етарли жавоб олмадим",
    "Жавоб кутяпман",
    "Жавоб олдим"
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
            $id = str_replace ('withoutslashes', '/', $id);

            $clientHash = explode("___", $id);

            $call_id = $clientHash[0];
            $hash = Hash::make($call_id);
            
            if (Hash::check($call_id, $clientHash[1])) {
                return view('feedback', compact('call_id'));
            }else{
                abort(404);
            }
        } catch (\Throwable $th) {
            abort(404);
        }
        
    }

    public function store(Request $request){
        
        // $existFeedback = Feedback::where('call_id', $request->call_id)->first();
        
        // if (empty($existFeedback)) {
        //     $request->validate([
        //         'complaint'=>'required',
        //         'not_solved'=>'required'
        //     ]);

        //     $feedback = Feedback::create([
        //         'call_id' => $request->call_id,
        //         'complaint' => $request->complaint,
        //         'advice' => '',
        //         'solved' => $request->not_solved,
        //     ]);
        //     if ($feedback->id) {
        //         return redirect()->route('feedback.success');
        //     }
        // }else{
        //     abort(404);
        // }

        $existFeedback = Feedback::where('call_id', $request->call_id)->first();
        if (empty($existFeedback)) {
            $feedback = Feedback::create([
                'call_id' => $request->call_id,
                'complaint' => '',
                'advice' => '',
                'solved' => $request->solved,
            ]);

            $message_id = 0;
            if ($feedback->id) {

                $infoCall = Call::find($request->call_id);
                $operator = Operator::find($infoCall->operator_id);

                $text = new Text();
	
                $text->appendEntity("Operator: ", "bold")->appendText($operator->name)->endl();
                $text->appendEntity("Telefon nomer: ", "bold")->appendText($infoCall->client_telephone)->endl();
                $text->appendEntity("Baho: ", "bold")->appendText(STATUS[$request->solved])->endl();
                $text->appendEntityUrl("Audio:  ", "bold")->appendText($infoCall->pbx_audio_url)->endl();
                $text->endl();

                $ch = curl_init(BOT_URL."sendMessage");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json"
                ]);

                $request = [
                    "chat_id" => TG_USER_CHANNEL,
                    "text" => $text->text,
                    "entities" => $text->entities,
                ];

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
                $response = curl_exec($ch);
                curl_close($ch);

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
	
                $text->appendEntity("Operator: ", "bold")->appendText($operator->name)->endl();
                $text->appendEntity("Telefon nomer: ", "bold")->appendText($infoCall->client_telephone)->endl();
                $text->appendEntity("Baho: ", "bold")->appendText(STATUS[$feedback->solved])->endl();
                $text->appendEntity("Izoh: ", "bold")->appendText($request->complaint)->endl();
                $text->appendEntityUrl("Audio:  ", "bold")->appendText($infoCall->pbx_audio_url)->endl();
                $text->endl();

                $ch = curl_init(BOT_URL."editMessageText");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json"
                ]);

                $request = [
                    "chat_id" => TG_USER_CHANNEL,
                    "message_id" => intval($request->message_id),
                    "text" => $text->text,
                    "entities" => $text->entities,
                ];

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
                $response = curl_exec($ch);
                curl_close($ch);
            }
            return Response::json($feedback);
        }else{
            return Response::json([]);
        }
    }

    public function success(){
        return view('success');
    }

    public function all(){
        $allFeedback = Feedback::paginate(10);
        return view('admin.feedback', compact('allFeedback'));
    }

    public function bot()
    {
        $text = new Text();
	
        $text->appendEntity("fffffffff", "bold")->endl()->endl();
        $text->appendEntity("gggggggg", "bold")->endl()->endl();
        $text->appendEntity("hhhhhhhhh", "bold")->endl()->endl();
        $text->appendEntityUrl("eeee ", "bold")->appendText("https://pbx12127.onpbx.ru/download_amocrm/eyJ1IjoiYWQwZmMyYTUtN2U4Yy00N2UxLWI5N2EtNGIzYWE3MDdmOTZmIiwiZCI6OTAsInNzIjoxNzA1MDU1ODEwLCJmIjoiOTA1NDQwNzAxIiwidCI6IjExMCIsImlkIjoieFVhS2dJTndHeGJvTEVRRCJ9_pVf2cvQQJ6IJJfVC+X9qjYMhq5542n48y6Py1ViyN4M=/rec.mp3")->endl();
        $text->endl();

        $ch = curl_init(BOT_URL."editMessageText");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        $request = [
            "chat_id" => TG_USER_CHANNEL,
            "message_id" => 22,
            "text" => $text->text,
            "entities" => $text->entities,
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        $response = curl_exec($ch);
        curl_close($ch);

        dd(json_decode($response));
    }
}
