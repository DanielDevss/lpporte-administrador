<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentIntentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Suscription;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

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

    /**
     * LINK: Guardar la venta
     * Se guarda la venta y se genera el checkout de pago para reenviarlo al cliente.
     */

    public function store (OrderStoreRequest $request) {
        try {            
            DB::beginTransaction();

            $folio = 'F_' . now()->format('ymdhis');
            $user = $request->user();
            $session = $this->createCheckout($request, $folio);
            $products = [];
            $totalQuantity = $this->calcTotalQuantity($request->products);
            $plan = $user->customer->currentPlan();

            foreach($request->products as $product) {
                $currentPlan = $plan === "ninguno" && $totalQuantity >= 10 ? "mayoreo" : $plan;
                $modelProduct = Product::find($product['id']);
                $products[] = [
                    'product_id' => $modelProduct->id,
                    'plan' => $currentPlan,
                    'amount' => $modelProduct->getCurrentPriceByPlan($currentPlan)
                ];
            }

            $order = Order::create([
                'folio' => $folio,
                'customer_id' => $user->customer->id,
                'amount' => $session->amount_total,
                'status' => $session->status,
                'stripe_session_id' => $session->id,
            ]);

            $order->products()->sync($products);

            DB::commit();

            return response()->json([
                'checkout' => $session->id,
                'url' => $session->url,
            ], 201);
            
        } catch (\Exception $e) {

            Log::error("Error al obtener ordenes: {$e->getMessage()}");

            DB::rollBack();

            return response()->json([
                "code" => 500,
                "message" => "Ocurrio un error interno"
            ], 500);
        }
    }

    /**
     * TODO: Actualizar y confirmar el estado de venta
     * Se actualiza el estado de venta de la base de datos con la información obtenida del Stripe Session
     */

    public function succeded (string $folio) {
        try {
            $order = Order::where('folio', $folio)->first();
            
            if(!$order) {
                Log::warning('No se encontro una orden con folio ' . $folio);
                return response()->json([
                    'message' => 'No se ha encontrado una orden con este número de folio'
                ], 404);
            }

            if($order->status === PaymentIntentStatusEnum::Succeeded->value){
                return response()->json(['message' => 'Esta orden ya no se puede modificar'], 402);
            }
            
            $stripe = new StripeService();
            $session = $stripe->findSession($order?->stripe_session_id);
            $paymentIntent = $stripe->findPaymentIntent($session?->payment_intent);

            $order->stripe_payment_id = $paymentIntent->id;
            $order->status = $paymentIntent->status;

            if($paymentIntent->status == 'succeeded') {
                foreach($order->products as $product) {
                    $product->stock -= $product->pivot->quantity;
                    $product->save();
                }
            }

            $order->save();

            return response()->json([
                'message' => 'La compra se completo de forma correcta',
                'redirect' => config('app.url') . '/' . $order->folio . '/download-ticket'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al confirmar la compra con folio ' . $folio, ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Ocurrio un error interno'
            ]);
        }
    }

    /**
     * NOTE: Generador del checkout de pago
     * Aqui estamos generando el enlace de pago y el id del pago con el que se guardara en la base de datos
     */

    private function createCheckout($request, $folio) {
        $lineItems = [];

            if(!$request->has('suscription')) {
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
                'success_url' => config('app.store_url') . '/' . $folio . '/compra-completa',
                'cancel_url' => config('app.store_url') . '/' . $folio . '/compra-cancelada',
                'line_items' => $lineItems,
                'mode' => 'payment'
            ]);

            return $session;
    }

    private function calcTotalQuantity (array $products = []):int {
        $totalQuantity = array_sum(array_column(
            $products, 
            'quantity'
        ));

        return $totalQuantity;
    }

}
