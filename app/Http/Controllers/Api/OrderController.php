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

class OrderController extends Controller
{
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
     * Guardar la venta y generar el Checkout de Stripe
     */
    public function store(OrderStoreRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $folio = 'F_' . now()->format('ymdhis');
                $user = $request->user();
                $plan = $user->customer->currentPlan();

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
                    // amount real lo ajustará el webhook; si viene amount_total lo usamos
                    'amount' => (int) ($session->amount_total ?? 0),
                    'status' => 'open', // el estado real lo pondrá el webhook
                    'stripe_session_id' => $session->id,
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
                'code' => 500,
                'message' => 'Ocurrió un error interno',
            ], 500);
        }
    }

    /**
     * Respaldo manual para confirmar estado (idealmente usa solo el webhook)
     */
    public function validateOrder(string $folio)
    {
        try {
            $order = Order::where('folio', $folio)->first();

            if (!$order) {
                Log::warning('No se encontró una orden con folio ' . $folio);
                return response()->json([
                    'message' => 'No se ha encontrado una orden con este número de folio',
                ], 404);
            }

            if ($order->status === PaymentIntentStatusEnum::Succeeded->value) {
                return response()->json(['message' => 'Esta orden ya no se puede modificar'], 409);
            }

            $stripe = new StripeService();
            $session = $stripe->findSession($order->stripe_session_id);
            $paymentIntent = $stripe->findPaymentIntent(
                is_string($session?->payment_intent)
                ? $session->payment_intent
                : ($session?->payment_intent?->id ?? null)
            );

            if (!$paymentIntent) {
                return response()->json([
                    'message' => 'No se pudo obtener el PaymentIntent',
                ], 400);
            }

            // Actualizar order con datos definitivos
            $order->stripe_payment_id = $paymentIntent->id;
            $order->status = $paymentIntent->status;
            $order->stripe_payment_method = $paymentIntent->payment_method ?? null;

            // Si la sesión trae amount_total, actualiza monto e impuesto
            if (isset($session->amount_total)) {
                $order->amount = (int) $session->amount_total;
                $order->tax = (int) round($order->amount * 0.16);
            }

            // Descontar stock solo si succeeded y no se ha hecho antes
            if ($paymentIntent->status === PaymentIntentStatusEnum::Succeeded->value && empty($order->sold_since)) {
                foreach ($order->products as $product) {
                    $qty = (int) ($product->pivot->quantity ?? 0);
                    if ($qty > 0) {
                        $product->decrement('stock', $qty);
                    }
                }
                $order->sold_since = now();
            }

            $order->save();

            return response()->json([
                'message' => 'La compra se completó de forma correcta',
                'redirect' => config('app.url') . '/' . $order->folio . '/download-ticket',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al confirmar la compra con folio ' . $folio, ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Ocurrió un error interno',
            ], 500);
        }
    }

    /**
     * Genera la Checkout Session con identificadores para el webhook
     */
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
            'success_url' => config('app.store_url') . '/orden/' . $folio . '/compra-completa',
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
}
