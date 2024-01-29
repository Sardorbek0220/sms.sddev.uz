<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Feedback;
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
            return Response::json($feedback);
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
}
