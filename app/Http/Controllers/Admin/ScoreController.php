<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Score;

class ScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $scores = Score::get();
        $keys = Score::keys;
        foreach ($scores as &$score) {
            $score->value = (array) json_decode($score->value);
        }
        return view('admin.score.index', compact('scores', 'keys'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $keys = Score::keys;
        return view('admin.score.create', compact('keys'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        if (in_array($request->key, ['missed', 'workly_ontime', 'workly_late'])) {
            Score::create([
                'key_text' => $request->key,
                'value' => json_encode($request->data),
                'from_date' => $request->from." 00:00:00",
                'to_date' => $request->to." 23:59:59",
            ]);
        }else{
            Score::create([
                'key_text' => $request->key,
                'value' => json_encode(['value' => $request->value]),
                'from_date' => $request->from." 00:00:00",
                'to_date' => $request->to." 23:59:59",
            ]);
        }
        
        return redirect()->route('scores.index');
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
        $score = Score::find($id);
        $score->value = (array) json_decode($score->value);
        $keys = Score::keys;
        return view('admin.score.edit', compact('score', 'keys'));
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
        $score = Score::find($id);
        if ($request->data) {
            $score->update([
                'value' => json_encode($request->data),
                'from_date' => $request->from." 00:00:00",
                'to_date' => $request->to." 23:59:59",
            ]);
        }else{
            $score->update([
                'value' => json_encode(['value' => $request->value]),
                'from_date' => $request->from." 00:00:00",
                'to_date' => $request->to." 23:59:59",
            ]);
        }

        return redirect()->route('scores.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $score = Score::find($id);
        $score->delete();
        return redirect()->route('scores.index');
    }
}
