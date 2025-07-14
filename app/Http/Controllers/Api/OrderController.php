<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
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

}
