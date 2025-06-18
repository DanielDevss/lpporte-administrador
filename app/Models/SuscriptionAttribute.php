<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuscriptionAttribute extends Model
{
    protected $fillable = [
        'suscription_id',
        'position',
        'text'
    ];
}
