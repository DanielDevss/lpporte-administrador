<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Suscription;
use App\Services\StripeService;
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

            $lineItems = [];

            if($request->has('suscription')) {
                $products = $request->products;
                $suscriptionId = $request->user()->customer->suscription_id;
                $totalQuantity = array_sum(array_column(
                    $products, 
                    'quantity'
                ));
    
                foreach ($products as $product) {
                    $findProduct = Product::find($product['id']);
                    if($findProduct) {
                        $stripePriceId = $findProduct->getCustomerPriceId(
                            $suscriptionId, 
                            $totalQuantity
                        );
                        $lineItems[] = [
                            'price' => $stripePriceId,
                            'quantity' => $product['quantity']
                        ];
                    }
                }
            }else{
                $suscription = Suscription::where('id',$request->suscription)
                    ->first();
                $lineItems[] = [
                    'price' => $suscription->stripe_price_id,
                    'quantity' => 1
                ];
            }


            $stripe = new StripeService();
            $session = $stripe->createCheckout([
                'success_url' => config('app.url') . '/success',
                'cancel_url' => config('app.url') . '/cancel',
                'line_items' => $lineItems,
                'mode' => 'payment'
            ]);

            return response()->json([
                'checkout' => $session->id,
                'url' => $session->url
            ], 201);
            
        } catch (\Exception $e) {

            Log::error("Error al obtener ordenes: {$e->getMessage()}");

            return response()->json([
                "code" => 500,
                "message" => "Ocurrio un error interno"
            ], 500);
        }
    }

}
