<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Training;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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

        if (empty($request->operator)) {
            $trainings = Training::whereBetween('date', [$from_date." 00:00:00", $to_date." 23:59:59"])->paginate(20);
        }else{
            $trainings = Training::where('operator', $request->operator)->whereBetween('date', [$from_date." 00:00:00", $to_date." 23:59:59"])->paginate(20);
        }

        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;

        return view('admin.training.index', compact('operators', 'trainings', 'from_date', 'to_date'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;
        return view('admin.training.create', compact('operators'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Training::create([
            'operator' => $request->operator,
            'comment' => $request->comment,
            'training' => $request->training,
            'date' => $request->date
        ]);
        
        return redirect()->route('trainings.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $training = Training::find($id);

        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;
        return view('admin.training.edit', compact('training', 'operators'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {        
        $Training = Training::find($id);
        $Training->update([
            'operator' => $request->operator,
            'comment' => $request->comment,
            'date' => $request->date,
            'training' => $request->training
        ]);

        return redirect()->route('trainings.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Training = Training::find($id);
        $Training->delete();
        return redirect()->route('trainings.index');
    }
}
