<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exception;

class ExceptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $exceptions = Exception::paginate(10);        
        return view('admin.exception.index', compact('exceptions'));
        // return view('admin.bitrix.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.exception.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $exc = Exception::create([
            'from_exc' => $request->from,
            'to_exc' => $request->to,
            'day' => $request->day,
        ]);    
           
        return redirect()->route('exceptions.index');
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
        $exception = Exception::find($id);
        return view('admin.exception.edit', compact('exception'));
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
        $exception = Exception::find($id);
        $exception->update([
            'from_exc' => $request->from,
            'to_exc' => $request->to,
            'day' => $request->day,
        ]);

        return redirect()->route('exceptions.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $exception = Exception::find($id);
        $exception->delete();
        return redirect()->route('exceptions.index');
    }
}
