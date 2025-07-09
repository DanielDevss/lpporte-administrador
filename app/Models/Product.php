<?php

    namespace App\Models;

    use App\Services\StripeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

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
            'stripe_product_id',
            'price',
            'price_wholesale',
            'price_basic_plan',
            'price_premium_plan',
            'stripe_price_id',
            'stripe_price_wholesale_id',
            'stripe_price_basic_plan_id',
            'stripe_price_premium_plan_id',
        ];

        protected $casts = [
            'stock' => 'integer',
        ];

        public function getRouteKeyName()
        {
            return 'slug';
        }

        /**
         * Scopes
         */

        public function scopePublics (Builder $query) {
            return $query->where('status', 'activo');
        }

        public function scopeOffPublics (Builder $query) {
            return $query->where('status', 'pausado');
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

        public function images() {
            return $this->belongsTo(ProductImage::class);
        }

        /**
         * Boot
         */

        protected static function boot()
        {
            parent::boot();

            static::creating(function ($data) {

                $stripe = new StripeService();

                $thumb = config('app.url') . $data->thumb;

                $data->slug = Str::slug($data->title) . '-' . Str::random(4);

                $stripeProductOptions = [
                    'name' => $data->title,
                    'description' => $data->description_short ?? null,
                    'images' => [$thumb]
                ];

                if(config('app.debug')) {
                    $stripeProductOptions['images'] = [];
                }

                $stripeProduct = $stripe->createProduct($stripeProductOptions);
                $data->stripe_product_id = $stripeProduct->id;

                $prices = ['price', 'price_wholesale', 'price_basic_plan', 'price_premium_plan'];

                foreach ($prices as $price) {
                    if ($data->$price > 0) {
                        $stripePrice = $stripe->createPrice([
                            'unit_amount' => $data->$price,
                            'currency' => config('services.stripe.currency'),
                            'product' => $stripeProduct->id,
                        ]);

                        $data->{'stripe_' . $price . '_id'} = $stripePrice->id;
                    }
                }

            });

            static::updating(function ($data) {
                // Actualizar el precio del producto

                // Actualizar la miniatura
                if ($data->isDirty('thumb')) {
                    $this->deleteImage();
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

        public function formatApiList () {
            return [
                'id' => $this->id,
                'slug' => $this->slug,
                'title' => $this->title,
                'stock' => $this->stock,
                'cover' => config('app.url') . '/storage/' . $this->thumb,
                'price' => $this->price / 100,
                'wholesale_price' => $this->price_wholesale / 100,
                'price_basic_plan' => $this->price_basic_plan / 100,
                'price_premium_plan' => $this->price_premium_plan / 100,
                'detail' => config('app.url') . '/api/products/' . $this->id,
            ];
        }

        public function formatApiDetail() {
            $images = [[
                'alt' => $this->title, 
                'src' => config('app.url') . '/storage/' . $this->thumb
            ]];

            $othersImages = $this->images ? $this->images->map(fn($img) => [
                    'alt' => $img->alt ?? $this->title,
                    'src' => config('app.url') . '/storage/' . $img->src
                ]) : [];

            $images = array_merge($images, $othersImages);

            return [
                'id' => $this->id,
                'slug' => $this->slug,
                'title' => $this->title,
                'description_short' => $this->description_short,
                'description' => $this->description,
                'stock' => $this->stock,
                'price' => $this->price / 100,
                'price_wholesale' => $this->price_wholesale / 100,
                'price_basic_plan' => $this->price_basic_plan / 100,
                'price_premium_plan' => $this->price_premium_plan / 100,
                'images' => $images
            ];
        }
    }
