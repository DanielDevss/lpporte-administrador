<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'brand'
    ];

    // Booting

    protected static function booted()
    {
        static::deleting(function ($brand) {
            $brand->deleteImage();
        });
    }

    // Relacion con productos

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Eliminar imagen

    public function deleteImage()
    {
        $storage = Storage::disk('public');
        if ($storage->exists($this->brand)) {
            $storage->delete($this->brand);
        }
    }

}
