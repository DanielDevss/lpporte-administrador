<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'cp',
        'state',
        'col',
        'city',
        'street',
        'no_ext',
        'no_int',
        'street_ref_1',
        'street_ref_2',
        'street_ref_3',
        'street_ref_4',
        'ref_address',
        'main'
    ];

    protected $casts = [
        'main' => 'boolean',
    ];

    // Relaciones

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

}
