<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'address_id',
        'folio',
        'status',
        'amount',
        'sold_since',
        'tax',
        'stripe_session_id',
        'stripe_payment_id',
        'stripe_payment_method',
        'activated_at'
    ];

    protected $casts = [
        'activated_at' => 'datetime'
    ];

    public function getRouteKeyName()
    {
        return 'folio';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->tax = $model->amount * 0.16;
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'orders_has_products')
            ->withPivot(['plan', 'amount', 'quantity']);
    }

    public function suscriptions()
    {
        return $this->belongsToMany(Suscription::class, 'orders_has_suscriptions')
            ->withPivot([
                'date_expired_suscription'
            ]);
    }

}
