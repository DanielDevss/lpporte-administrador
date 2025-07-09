<?php

namespace App\Models;

use App\Services\StripeService;
use Illuminate\Database\Eloquent\Model;

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
                'recurring' => [
                    'interval' => 'year',
                    'interval_count' => 1,
                    'usage_type' => 'licensed',
                ],
            ]);
            $model->stripe_product_id = $stripeProduct->id;
            $model->stripe_price_id = $stripePrice->id;
        });

        static::updating(function ($data) {
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
