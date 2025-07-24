<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function index (Request $request) {
        try {
            
            $customer = $request->user()->customer;
            $orders = Order::where('customer_id', $customer->id)->get();

            return response()->json($orders);
        }
        catch (\Exception $e) {

            Log::error("Error al obtener ordenes: {$e->getMessage()}");

            return response()->json([], $e->getCode());
        }
    }


    public function store (OrderStoreRequest $request) {
        try {

            $productsId = [];
            $products = $request->products;
            $suscriptionId = $request->user()->customer->suscription_id;

            foreach ($products as $product) {
                $productsId[] = $product['id'];
            }

            $orderProducts = Product::whereIn('id', $productsId)->get();
            
            return response()->json([
                'suscriptionId' => $suscriptionId
            ]);
            
        } catch (\Exception $e) {

            Log::error("Error al obtener ordenes: {$e->getMessage()}");

            return response()->json([
                "code" => 500,
                "message" => "Ocurrio un error interno"
            ], $e->getCode());
        }
    }

}
