<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suscription extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'free',
        'stripe_price_id',
        'stripe_product_id'
    ];

    /**
     * Relaciones con tablas
     */

    public function attributes () {
        return $this->hasMany(SuscriptionAttribute::class);
    }

}
