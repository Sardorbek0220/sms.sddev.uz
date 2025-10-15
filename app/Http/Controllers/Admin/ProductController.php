<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use Carbon\Carbon; // Ensure this is included at the top of your controller
use App\Request_type;

class ProductController extends Controller
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
            $products = Product::whereBetween('date', [$from_date." 00:00:00", $to_date." 23:59:59"])->get();
        }else{
            $products = Product::where('operator', $request->operator)->whereBetween('date', [$from_date." 00:00:00", $to_date." 23:59:59"])->get();
        }

        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;
        $routeName = $request->route()->getName();
        // dump($routeName);
        return view($routeName == 'products.index' ? 'admin.product.index' : 'operator.product', compact('operators', 'products', 'from_date', 'to_date'));
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

        $request_types = Request_type::get();

        return view('admin.product.create', compact('operators', 'request_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        foreach ($request->data as $datum) {
            if (empty($datum['script']) || empty($datum['product'])) {
                continue;
            }
            Product::create([
                'operator' => $datum['operator'],
                'comment' => $datum['comment'],
                'client_phone' => $datum['client_phone'],
                'audio_url' => $datum['audio_url'],
                'request_type_id' => $datum['request_type_id'],
                'request' => $datum['requestt'],
                'response' => $datum['response'],
                'date' => $datum['date'],
                'script' => $datum['script'],
                'product' => $datum['product'],
                'solution' => $datum['solution'] ?? 0,
                'principle_1' => $datum['principle_1'] ?? 0,
                'principle_2' => $datum['principle_2'] ?? 0,
                'principle_3' => $datum['principle_3'] ?? 0,
                'principle_4' => $datum['principle_4'] ?? 0
            ]);
        }
        
        return redirect()->route('products.index');
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
        $product = Product::find($id);

        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;

        $request_types = Request_type::get();

        return view('admin.product.edit', compact('product', 'operators', 'request_types'));
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
        $product = Product::find($id);
        $product->update([
            'comment' => $request->comment,
            'client_phone' => $request->client_phone,
            'audio_url' => $request->audio_url,
            'request_type_id' => $request->request_type_id,
            'request' => $request->requestt,
            'response' => $request->response,
            'date' => $request->date,
            'script' => $request->script,
            'product' => $request->product,
            'solution' => $request->solution ?? 0,
            'principle_1' => $request->principle_1 ?? 0,
            'principle_2' => $request->principle_2 ?? 0,
            'principle_3' => $request->principle_3 ?? 0,
            'principle_4' => $request->principle_4 ?? 0
        ]);

        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();
        return redirect()->route('products.index');
    }
}
