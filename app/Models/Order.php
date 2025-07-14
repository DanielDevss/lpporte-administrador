<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'folio',
        'status',
        'amount',
        'sold_since',
        'tax',
        'stripe_payment_id',
        'stripe_payment_method'
    ];

    public function customer () {
        return $this->belongsTo(Customer::class);
    }

    public function products () {
        return $this->belongsToMany(Product::class, 'order_has_products')
            ->withPivot(['plan']);
    }
}
