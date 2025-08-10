<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Suscription;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // LINK obtener ventas del usuario

    public function index(Request $request)
    {
        try {
            $customer = $request->user()->customer;

            $orders = Order::with('products', 'suscriptions')
                ->where('customer_id', $customer->id)
                ->latest()
                ->get()
                    ?->map(fn($order) => [
                    'id' => $order->id,
                    'folio' => $order->folio,
                    'amount' => $order->amount,
                    'created_at' => $order->created_at->format('d/m/Y h:i a'),
                    'status' => $order->status,
                    'products' => $order->products->map(fn($product) => [
                        'id' => $product->id,
                        'slug' => $product->slug,
                        'title' => $product->title,
                        'amount' => $product->pivot->amount,
                        'quantity' => (int) ($product->pivot->quantity ?? 0),
                    ]),
                    'suscription' => $order->suscriptions
                        ? $order->suscriptions()->select('name')->first()?->name
                        : null,
                    'download' => route('download.ticket', ['folio' => $order->folio]),
                ]);

            return response()->json($orders ?? []);
        } catch (\Throwable $e) {
            Log::error("Error al obtener órdenes: {$e->getMessage()}");
            return response()->json([], 500);
        }
    }

    /**
     * SECTION Generador del checkout
     * -------------------------------------------------------
     * Creamos el checkout con stripe y la orden en nuestra
     * base de datos para tener el historial. 
     * Almacenamos el checkout_id para poderlo usar en otro 
     * momento para validar la compra.
     */

    // LINK main

    public function store(OrderStoreRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $folio = 'F_' . now()->format('ymdhis');
                $user = $request->user();
                $plan = $user->customer->currentPlan();
                $addressId = $user
                    ->customer
                    ->addresses()
                    ->where('main', true)
                    ->first()?->id;
                if (!$addressId) {
                    Log::warning('El cliente ' . $user->customer->id . ', no tiene dirección principal.');
                    return response()->json([
                        'success' => false,
                        'message' => 'Agrega un dirección a tu cuenta'
                    ], 422);
                }

                // 1) Preparar line items y pivot map
                $productsMap = [];

                if (!$request->has('suscription')) {
                    $totalQuantity = $this->calcTotalQuantity($request->products);

                    foreach ($request->products as $p) {
                        $modelProduct = Product::find($p['id']);
                        if (!$modelProduct) {
                            continue;
                        }
                        $currentPlan = $plan === 'ninguno' && $totalQuantity >= 10 ? 'mayoreo' : $plan;

                        $productsMap[$modelProduct->id] = [
                            'plan' => $currentPlan,
                            'amount' => $modelProduct->getCurrentPriceByPlan($currentPlan),
                            'quantity' => (int) $p['quantity'],
                        ];
                    }
                }

                // 2) Crear la Checkout Session con identificadores
                $session = $this->createCheckout($request, $folio);

                // 3) Crear Order
                $order = Order::create([
                    'folio' => $folio,
                    'customer_id' => $user->customer->id,
                    'amount' => (int) ($session->amount_total ?? 0),
                    'status' => 'open',
                    'stripe_session_id' => $session->id,
                    'address_id' => $addressId
                ]);

                // 4) Guardar relación productos / suscripción
                if (!empty($productsMap)) {
                    $order->products()->sync($productsMap);
                }

                if ($request->has('suscription')) {
                    $order->suscriptions()->sync([(int) $request->suscription]);
                }

                // 5) Respuesta con URL de checkout
                return response()->json([
                    'checkout' => $session->id,
                    'url' => $session->url,
                ], 201);
            });
        } catch (\Throwable $e) {
            Log::error("Error al crear la orden: {$e->getMessage()}");
            return response()->json([
                'message' => 'Ocurrió un error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // LINK Crear checkout

    private function createCheckout(Request $request, string $folio)
    {
        $lineItems = [];

        if (!$request->has('suscription')) {
            $products = $request->products;
            $suscriptionId = $request->user()->customer->suscription_id;
            $totalQuantity = array_sum(array_column($products, 'quantity'));

            foreach ($products as $p) {
                $findProduct = Product::find($p['id']);
                if ($findProduct) {
                    $stripePriceId = $findProduct->getCustomerPriceId($suscriptionId, $totalQuantity);
                    $lineItems[] = [
                        'price' => $stripePriceId,
                        'quantity' => (int) $p['quantity'],
                    ];
                }
            }
        } else {
            $suscription = Suscription::find($request->suscription);
            if ($suscription) {
                $lineItems[] = [
                    'price' => $suscription->stripe_price_id,
                    'quantity' => 1,
                ];
            }
        }

        $stripe = new StripeService();
        $payload = [
            'success_url' => config('app.store_url') . '/orden/' . $folio . '/validacion',
            'cancel_url' => config('app.store_url') . '/orden/' . $folio . '/compra-cancelada',
            'line_items' => $lineItems,
            'mode' => 'payment',

            // 👇 Claves para mapear orden en el webhook
            'client_reference_id' => $folio,
            'metadata' => [
                'folio' => $folio,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'folio' => $folio,
                ],
            ],
        ];

        return $stripe->createCheckout($payload);
    }

    private function calcTotalQuantity(array $products = []): int
    {
        return array_sum(array_map(fn($p) => (int) ($p['quantity'] ?? 0), $products));
    }


    // !SECTION

    /**
     * SECTION Validación de compra
     * -------------------------------------------------------
     * Verificamos si la compra se reflejo en Stripe y
     * descontamos del inventario de productos o activamos
     * la suscripcion del usuario
     */

    // LINK main

    public function validateOrder(string $folio)
    {
        $order = Order::where('folio', $folio)
            ->whereNotIn('status', [
                OrderStatusEnum::Succeeded->value,
                OrderStatusEnum::Canceled->value,
                OrderStatusEnum::Denied->value,
            ])
            ->firstOrFail();

        try {
            $stripe = new StripeService();
            $session = $stripe->findSession($order->stripe_session_id);
            $sessionPaymentId = $session?->payment_intent;

            if (!$session || !$sessionPaymentId) {
                Log::warning('session o payment_intent no encontrado en orden con folio ' . $folio);
                $order->status = OrderStatusEnum::Denied->value;
                $order->save();
                return response()->json([
                    'success' => false,
                    'message' => 'El pago no se completo de forma correcta',
                ], 402);
            }

            $paymentIntent = $stripe->findPaymentIntent($sessionPaymentId);
            $order->status = $paymentIntent->status;
            $order->save();

            $response = $order->suscriptions()->exists()
                ? $this->validateSuscriptionAndActive($order)
                : $this->validateProducts($order->products, $folio);

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrio un error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // LINK validación de productos

    private function validateProducts($products = [], $folio): array
    {
        Log::info('Validando compra de productos para orden ' . $folio);
        foreach ($products as $product) {
            $product->stock -= $product->pivot->quantity;
            $product->save();
        }
        return [
            'success' => true,
            'message' => 'Compra de productos validada',
            'products' => $products
        ];
    }

    // LINK validación suscripcion

    private function validateSuscriptionAndActive(Order $order): array
    {
        Log::info('Validando pago de subscripción', ['folio' => $order->folio]);

        // Validar solo si la orden esta succeeded
        if ($order->status !== OrderStatusEnum::Succeeded->value) {
            return [
                'success' => false,
                'message' => 'El pago aún no esta completado'
            ];
        }

        // Evitar activar dos veces la misma orden
        if (!empty($order->activated_at)) {
            return [
                'success' => true,
                'message' => 'La suscripción ya había sido activada'
            ];
        }

        $suscription = $order->suscriptions()->first();
        $customer = $order->customer;

        if (!$suscription || !$customer) {
            Log::warning('Faltan datos para activar la suscripción', [
                'folio' => $order->folio,
                'has_suscription' => (bool) $suscription,
                'has_customer' => (bool) $customer
            ]);
            return [
                'success' => false,
                'message' => 'No se pudo activar la suscripción, hay datos incompleto'
            ];
        }

        $expiresAt = now()->addYear();

        DB::transaction(function () use ($expiresAt, $suscription, $customer, $order) {
            // Activamos la suscripcion
            $customer->forceFill([
                'suscription_active' => true,
                'suscription_id' => $suscription->id,
                'date_expired_suscription' => $expiresAt
            ])->saveQuietly();
            // Marcamos la orden como activada
            $order->forceFill([
                'activated_at' => now()
            ])->saveQuietly();
        });

        return [
            'success' => true,
            'message' => 'La suscripción ' . $suscription->name . ', se activo de forma correcta'
        ];
    }

    // !SECTION
}
