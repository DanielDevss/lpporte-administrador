<?php

namespace App\Models;

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
        'stripe_price_id',
        'stripe_product_id'
    ];

    protected $casts = [
        'attributes' => 'array'
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
        
        static::creating(function () {
            // TODO: Crear stripe price y product id

        });

        static::updating(function () {
            // TODO: Actualizar stripe price id en caso de que este cambie

        });
    }
}
