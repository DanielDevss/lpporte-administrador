<?php

namespace App\Models;

use App\Services\StripeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Suscription extends Model
{
    /**
     * Ajustes
     */
    protected $fillable = [
        'name',
        'amount',
        'free',
        'attributes',
        'benefits',
        'stripe_price_id',
        'stripe_product_id'
    ];

    protected $casts = [
        'attributes' => 'array',
        'benefits' => 'array',
        'free' => 'boolean',
    ];

    /**
     * Relaciones con tablas
     */
    public function productPrices () {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Operaciones boots
     */

    public static function boot() {
        parent::boot();
        
        static::creating(function ($model) {
            // TODO: Crear stripe price y product id
            $stripe = new StripeService();
            $stripeProduct = $stripe->createProduct([
                'name' => $model->name,
            ]);
            $stripePrice = $stripe->createPrice([
                'unit_amount' => $model->amount,
                'currency' => config('services.stripe.currency'),
                'product' => $stripeProduct->id,
            ]);
            $model->stripe_product_id = $stripeProduct->id;
            $model->stripe_price_id = $stripePrice->id;
        });

        static::updating(function ($model) {
            // Si el precio cambió, actualizar en Stripe
            if ($model->isDirty('amount')) {
                $stripe = new StripeService();

                // Crear nuevo precio
                $stripePrice = $stripe->createPrice([
                    'unit_amount' => $model->amount,
                    'currency' => config('services.stripe.currency'),
                    'product' => $model->stripe_product_id,
                ]);
                
                $model->stripe_price_id = $stripePrice->id;
            }
            
            // Si el nombre cambió, actualizar el producto en Stripe
            if ($model->isDirty('name')) {
                $stripe = new StripeService();
                
                try {
                    $stripe->updateProduct([
                        'name' => $model->name,
                    ], $model->stripe_product_id);
                } catch (\Exception $e) {
                    Log::warning("Error al actualizar producto en Stripe: {$model->stripe_product_id}", ['error' => $e->getMessage()]);
                }
            }
        });
    }

    // Otras funciones

    public function formatApiList () {
        $attrs = $this->getAttributes()['attributes'];
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount'=> $this->amount / 100,
            'benefits' => $this->benefits ?? [],
            'attributes' => $attrs ? json_decode($attrs, true) : []
        ];
    }
}
