<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;

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

        $products = Product::whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])->paginate(20);

        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;

        return view('admin.product.index', compact('operators', 'products', 'from_date', 'to_date'));
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
        return view('admin.product.create', compact('operators'));
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
            if (empty($request->client_phone[$id]) || empty($request->audio_url[$id]) || empty($request->script[$id]) || empty($request->product[$id])) {
                continue;
            }
            Product::create([
                'operator' => $id,
                'comment' => $request->comment[$id],
                'client_phone' => $request->client_phone[$id],
                'audio_url' => $request->audio_url[$id],
                'request' => $request->requestt[$id],
                'response' => $request->response[$id],
                'date' => $request->date[$id],
                'script' => $request->script[$id],
                'product' => $request->product[$id]
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
        return view('admin.product.edit', compact('product', 'operators'));
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
            'request' => $request->requestt,
            'response' => $request->response,
            'date' => $request->date,
            'script' => $request->script,
            'product' => $request->product
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
