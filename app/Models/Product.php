<?php

    namespace App\Models;

    use App\Services\StripeService;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Storage;

    class Product extends Model
    {
        protected $fillable = [
            'brand_id',
            'title',
            'slug',
            'description_short',
            'description',
            'thumb',
            'stock',
            'status',
            'stripe_product_id'
        ];

        public function getRouteKeyName()
        {
            return 'slug';
        }

        /**
         * Relaciones
         */

        public function brand () {
            return $this->belongsTo(Brand::class);
        }

        public function categories () {
            return $this->belongsToMany(Category::class, 'categories_has_products');
        }

        /**
         * Boot
         */

        protected static function boot()
        {
            parent::boot();

            static::creating(function ($model) {

                $stripe = new StripeService();

                $thumb = config('app.url') . $model->thumb;

                $stripe->createProduct([
                    'name' => $model->name,
                    'description' => $model->description_short,
                    'images' => [$thumb]
                ]);

                $model->stripe_product_id = null;
            });

            static::updating(function ($model) {
                if ($model->isDirty('thumb')) {
                    $model->deleteImage();
                }
            });


            static::deleting(function ($model) {
                $model->deleteImage();
            });
        }

        /**
         * Mas opciones
         */

        public function deleteImage()
        {
            $thumb_path = $this->thumb;
            $storage = Storage::disk('public');
            if ($storage->exists($thumb_path)) {
                $storage->delete($thumb_path);
            }
        }
    }
