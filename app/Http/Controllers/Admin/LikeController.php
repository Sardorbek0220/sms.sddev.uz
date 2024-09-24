<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Like;

class LikeController extends Controller
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

        $likes = Like::whereBetween('date', [$from_date." 00:00:00", $to_date." 23:59:59"])->paginate(20);

        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;

        return view('admin.like.index', compact('operators', 'likes', 'from_date', 'to_date'));
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
        return view('admin.like.create', compact('operators'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        foreach ($request->operator as $id => $name) {
            if (empty($request->comment[$id]) || !isset($request->punish[$id])) {
                continue;
            }
            Like::create([
                'operator' => $id,
                'comment' => $request->comment[$id],
                'punishment' => $request->punish[$id],
                'date' => $request->date[$id]
            ]);
        }
        
        return redirect()->route('likes.index');
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
        $like = Like::find($id);

        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;
        return view('admin.like.edit', compact('like', 'operators'));
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
        $like = Like::find($id);
        $like->update([
            'comment' => $request->comment,
            'date' => $request->date,
            'punishment' => $request->punish
        ]);

        return redirect()->route('likes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $like = Like::find($id);
        $like->delete();
        return redirect()->route('likes.index');
    }
}
