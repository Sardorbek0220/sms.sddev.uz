<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Response;
use Illuminate\Http\Request;
use App\Like;

class BigreportController extends Controller
{
    public function index(Request $request)
    {
        $auth = json_decode(file_get_contents("configs/auth.txt"));
        $key_and_id = $auth->key_id.":".$auth->key;
        $auth_key = "OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM";

        return view('admin.bigreport', compact('key_and_id', 'auth_key'));
    }

    public function piece(Request $request)
    {
        $auth = json_decode(file_get_contents("configs/auth.txt"));
        $key_and_id = $auth->key_id.":".$auth->key;
        $auth_key = "OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM";

        return view('admin.layouts.piece', compact('key_and_id', 'auth_key'));
    }

    public function extra(Request $request)
    {
        $from = $request['from'] . " 00:00:00";
        $to = $request['to'] . " 23:59:59";
        
        $likes = DB::table('likes')
            ->select(
                'operator', 
                DB::raw('SUM(CASE WHEN punishment = 1 THEN 1 ELSE 0 END) AS punishments'),
                DB::raw('SUM(CASE WHEN punishment = 0 THEN 1 ELSE 0 END) AS likes')
            )->whereBetween('date', [$from, $to])
            ->groupByRaw('operator')
            ->get();

        $products = DB::table('products')
            ->select('operator', DB::raw('AVG(script) AS avg_script'), DB::raw('AVG(product) AS avg_product'))
            ->whereBetween('date', [$from, $to])
            ->groupByRaw('operator')
            ->get();
        return Response::json(['products' => $products, 'likes' => $likes]);
    }
}
