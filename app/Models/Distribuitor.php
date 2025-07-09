<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Distribuitor extends Model
{
    protected $fillable = [
        "name",
        "phone",
        "photo",
        "address"
    ];

    protected static function boot() {
        parent::boot();
        static::deleted(function ($model) {
            if(Storage::disk('public')->exists($model->photo)) {
                Storage::disk('public')->delete($model->photo);
            }
        });
    }
}
