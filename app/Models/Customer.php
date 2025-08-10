<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
            $model->suscription_active ??= false;
            $model->reference_code ??= Str::random(12);
        });
    }

    /**
     * SECTION Relaciones con tablas
     */

    // LINK usuario

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // LINK suscripción

    public function suscription()
    {
        return $this->belongsTo(Suscription::class, 'suscription_id');
    }

    // LINK orden

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // LINK Direcciones

    public function addresses()
    {
        return $this->hasMany(Address::class, 'customer_id');
    }

    // !SECTION

    /**
     * SECTION Otras funciones
     */

    public function verifySuscription(): array
    {
        // Si no hay plan premium/básico activo, forzar baja a gratis
        if (!$this->suscription_active || $this->suscription_id <= 1) {
            $this->handleUnsubscribe();
            return [
                'active' => false,
                'suscription_id' => 1,
            ];
        }

        // Validar expiración (null = expirada)
        $isExpired = !$this->date_expired_suscription
            ? true
            : $this->date_expired_suscription->isPast();

        if ($isExpired) {
            $this->handleUnsubscribe();
            return [
                'active' => false,
                'suscription_id' => 1,
            ];
        }

        // Sigue vigente
        return [
            'active' => true,
            'suscription_id' => $this->suscription_id,
        ];
    }


    // LINK Dar de baja

    public function handleUnsubscribe()
    {
        $this->forceFill([
            'suscription_active' => false,
            'suscription_id' => 1,
        ])->saveQuietly();
    }

    // LINK Plan actual

    public function currentPlan()
    {
        $cases = [
            1 => 'ninguno',
            2 => 'basico',
            3 => 'premium'
        ];

        return $cases[$this->suscription_id] ?? 'ninguno';
    }

}
