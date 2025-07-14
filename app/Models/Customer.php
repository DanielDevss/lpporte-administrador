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
    ];

    protected $casts = [
        'suscription_active' => 'boolean',
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

    public function orders() {
        return $this->hasMany(Order::class);
    }

}
