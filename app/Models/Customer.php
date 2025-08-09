<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'suscription_id',
        'suscription_active',
        'reference_code',
        'date_expired_suscription'
    ];

    protected $casts = [
        'suscription_active' => 'boolean',
        'date_expired_suscription' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->suscription_active = false;
            $model->reference_code = now()->format('dmyhis');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suscription()
    {
        return $this->belongsTo(Suscription::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'customer_id');
    }

    public function currentPlan()
    {
        $cases = [
            1 => 'ninguno',
            2 => 'basico',
            3 => 'premium'
        ];

        return $cases[$this->suscription_id];
    }

}
