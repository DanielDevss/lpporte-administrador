<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'suscription_id',
        'amount',
        'tax',
        'stripe_price_id'
    ];

    /**
     * Relaciones con tablas
     */

    public function product () {
        return $this->belongsTo(Product::class);
    }

    /**
     * Operaciones Boot
     */
    public static function boot () {
        parent::booting();

        static::creating(function ($price) {
            $price->amount = $price->amount * 100;
            $price->tax = $price->amount * .16;
        });
    }

    /**
     * Otras operaciones
     */

}
