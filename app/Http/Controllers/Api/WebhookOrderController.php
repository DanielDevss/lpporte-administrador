<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentIntentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;

class WebhookOrderController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        if (!$sigHeader) {
            Log::warning('Stripe webhook sin Stripe-Signature');
            return response()->json(['error' => 'Missing signature'], 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook signature failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Maneja los tipos más relevantes
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object; // \Stripe\Checkout\Session
                $this->handleCheckoutCompleted($session);
                break;

            case 'payment_intent.succeeded':
            case 'payment_intent.payment_failed':
                // Opcional: soporta flujos que no pasan por Checkout
                $pi = $event->data->object; // \Stripe\PaymentIntent
                $this->handlePaymentIntentUpdate($pi);
                break;

            default:
                // otros eventos: invoice.paid, charge.refunded, etc.
                break;
        }

        return response()->json(['received' => true], 200);
    }

    protected function handleCheckoutCompleted(\Stripe\Checkout\Session $session): void
    {
        // Encontrar la orden por session id (la guardaste al crear la orden)
        /** @var Order|null $order */
        $order = Order::where('stripe_session_id', $session->id)->first();

        if (!$order) {
            Log::warning('Webhook: no se encontró Order por stripe_session_id', ['session_id' => $session->id]);
            return;
        }

        // Idempotencia: si ya está en succeeded, no tocar (evita descontar stock dos veces)
        if ($order->status === PaymentIntentStatusEnum::Succeeded->value) {
            return;
        }

        // Traer el PaymentIntent para tener status real y método de pago
        $client = new StripeClient(config('services.stripe.secret'));
        $paymentIntentId = is_string($session->payment_intent) ? $session->payment_intent : ($session->payment_intent->id ?? null);

        if (!$paymentIntentId) {
            // Puede ocurrir si es modo "setup" o similar
            Log::warning('Webhook: Session sin payment_intent', ['session_id' => $session->id]);
            return;
        }

        $pi = $client->paymentIntents->retrieve($paymentIntentId, ['expand' => ['charges']]);

        DB::transaction(function () use ($order, $session, $pi) {
            // Actualizar datos base
            $order->stripe_payment_id = $pi->id;
            $order->status = $pi->status; // e.g. 'succeeded', 'requires_payment_method', etc.
            $order->amount = $session->amount_total ?? $order->amount;
            $order->stripe_payment_method = $pi->payment_method ?? null;

            // Si succeeded => descuenta stock (una sola vez)
            if ($pi->status === PaymentIntentStatusEnum::Succeeded->value) {
                // Evita doble descuento si ya marcaste venta (usa sold_since como “bandera” si la tienes)
                if (empty($order->sold_since)) {
                    foreach ($order->products as $product) {
                        // quantity puede venir null si no lo guardaste en pivot; valida
                        $qty = (int) ($product->pivot->quantity ?? 0);
                        if ($qty > 0) {
                            $product->decrement('stock', $qty);
                        }
                    }
                    $order->sold_since = now();
                }
            }

            $order->save();
        });

        Log::info('Webhook: Checkout session completed procesada', [
            'order_id' => $order->id,
            'folio' => $order->folio,
            'pi' => $paymentIntentId,
            'status' => $order->status,
        ]);
    }

    protected function handlePaymentIntentUpdate(\Stripe\PaymentIntent $pi): void
    {
        // Si usas Checkout, lo normal es usar handleCheckoutCompleted().
        // Este método permite actualizar por PI si guardaste el payment_intent en la orden,
        // o si puedes mapear por metadata.

        // Opción A: buscar por payment id (si ya lo tenías guardado)
        $order = Order::where('stripe_payment_id', $pi->id)->first();

        // Opción B: si al crear la Session pusiste metadata ['folio' => ...], también puedes mapear por metadata
        if (!$order && isset($pi->metadata['folio'])) {
            $order = Order::where('folio', $pi->metadata['folio'])->first();
        }

        if (!$order) {
            Log::warning('Webhook PI: no se encontró Order por payment_intent/metadata', ['pi' => $pi->id]);
            return;
        }

        if ($order->status === PaymentIntentStatusEnum::Succeeded->value) {
            return;
        }

        DB::transaction(function () use ($order, $pi) {
            $order->stripe_payment_id = $pi->id;
            $order->status = $pi->status;
            $order->stripe_payment_method = $pi->payment_method ?? null;

            if ($pi->status === PaymentIntentStatusEnum::Succeeded->value && empty($order->sold_since)) {
                foreach ($order->products as $product) {
                    $qty = (int) ($product->pivot->quantity ?? 0);
                    if ($qty > 0) {
                        $product->decrement('stock', $qty);
                    }
                }
                $order->sold_since = now();
            }

            $order->save();
        });

        Log::info('Webhook: PaymentIntent update procesado', [
            'order_id' => $order->id,
            'folio' => $order->folio,
            'pi' => $pi->id,
            'status' => $order->status,
        ]);
    }
}
