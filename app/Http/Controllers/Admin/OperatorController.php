<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Operator;
use App\User;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $operator_id = User::where('email', 'operator@gmail.com')->first()['id'];
        $operators = Operator::get();        
        return view('admin.operator.index', compact('operators', 'operator_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.operator.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'phone'=>'required'
        ]);

        $exist = Operator::where('phone', $request->phone)->first();
        if (empty($exist)) {
            $operator = Operator::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'workly_id' => $request->workly_id,
                'active' => $request->active ? 'Y' : 'N',
                'field' => $request->field ? 1 : 0,
                'color' => $request->color ?? null
            ]);
        }else{
            $request->session()->flash('status', 'This phone number already created !!!');
        }
        return redirect()->route('operators.index');
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
        $operator = Operator::find($id);
        return view('admin.operator.edit', compact('operator'));
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
        $request->validate([
            'name'=>'required',
            'phone'=>'required'
        ]);

        $operator = Operator::find($id);
        $operator->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'workly_id' => $request->workly_id,
            'active' => $request->active ? 'Y' : 'N',
            'field' => $request->field ? 1 : 0,
            'color' => $request->color ?? null
        ]);

        return redirect()->route('operators.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $operator = Operator::find($id);
        $operator->update([
            'active' => 'N'
        ]);
        return redirect()->route('operators.index');
    }
}
