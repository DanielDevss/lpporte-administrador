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
    ];

    protected $casts = [
        'suscription_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suscription()
    {
        return $this->belongsTo(Suscription::class);
    }
}
