<?php

namespace App\Models;

use App\Services\StripeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
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

    protected static $prices = ['price', 'price_wholesale', 'price_basic_plan', 'price_premium_plan'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Scopes
     */

    public function scopePublics(Builder $query)
    {
        return $query->where('status', 'activo');
    }

    public function scopeOffPublics(Builder $query)
    {
        return $query->where('status', 'pausado');
    }

    /**
     * Relaciones
     */

    public function orders()
    {
        return $this->belongsToMany(
            Order::class,
            'orders_has_products'
        )
            ->withPivot([
                'plan',
            ]);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_has_products');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * Boot
     */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->generateSlug();
            $model->createStripeProduct();
            $model->createStripePrices();
        });

        static::updating(function ($model) {
            $model->updateStripeProduct();
            $model->updateStripePrices();
            $model->handleThumbnailChange();
        });

        static::deleting(function ($model) {
            $model->cleanupStripeResources();
            $model->deleteImage();
        });
    }

    /**
     * Métodos privados para el boot
     */

    private function generateSlug()
    {
        $this->slug = Str::slug($this->title) . '-' . Str::random(4);
    }

    private function createStripeProduct()
    {
        $stripe = new StripeService();

        $stripeProductOptions = [
            'name' => $this->title,
            'description' => $this->description_short ?? null,
            'images' => $this->getStripeImageArray()
        ];

        $stripeProduct = $stripe->createProduct($stripeProductOptions);
        $this->stripe_product_id = $stripeProduct->id;
    }

    private function createStripePrices()
    {
        $stripe = new StripeService();

        foreach (self::$prices as $priceField) {
            if ($this->$priceField > 0) {
                $stripePrice = $stripe->createPrice([
                    'unit_amount' => $this->$priceField,
                    'currency' => config('services.stripe.currency'),
                    'product' => $this->stripe_product_id,
                ]);

                $this->{'stripe_' . $priceField . '_id'} = $stripePrice->id;
            }
        }
    }

    private function updateStripeProduct()
    {
        if (!$this->isDirty(['title', 'description_short', 'description', 'thumb'])) {
            return;
        }

        $stripe = new StripeService();

        $stripeProductOptions = [
            'name' => $this->title,
            'description' => $this->description_short ?? null,
            'images' => $this->getStripeImageArray()
        ];

        $stripe->updateProduct($stripeProductOptions, $this->stripe_product_id);
    }

    private function updateStripePrices()
    {
        $stripe = new StripeService();

        foreach (self::$prices as $priceField) {
            if (!$this->isDirty($priceField)) {
                continue;
            }

            $stripePriceIdField = 'stripe_' . $priceField . '_id';
            $newPrice = $this->$priceField;

            if ($newPrice > 0) {
                $this->updateOrCreateStripePrice($stripe, $priceField, $stripePriceIdField, $newPrice);
            } else {
                $this->deactivateStripePriceInternal($stripe, $stripePriceIdField);
                $this->$stripePriceIdField = null;
            }
        }
    }

    private function updateOrCreateStripePrice($stripe, $priceField, $stripePriceIdField, $newPrice)
    {
        // Obtener el producto original para comparar
        $originalProduct = static::find($this->id);

        if ($originalProduct && $originalProduct->$stripePriceIdField) {
            // Desactivar precio anterior
            $this->deactivateStripePriceInternal($stripe, $originalProduct->$stripePriceIdField);
        }

        // Crear nuevo precio
        $stripePrice = $stripe->createPrice([
            'unit_amount' => $newPrice,
            'currency' => config('services.stripe.currency'),
            'product' => $this->stripe_product_id,
        ]);

        $this->$stripePriceIdField = $stripePrice->id;
    }

    private function deactivateStripePriceInternal($stripe, $priceId)
    {
        if (!$priceId) {
            return;
        }

        try {
            $stripe->deactivatePrice($priceId);
        } catch (\Exception $e) {
            // Log del error si es necesario, pero continuar
            Log::warning("Error al desactivar precio de Stripe: {$priceId}", ['error' => $e->getMessage()]);
        }
    }

    private function handleThumbnailChange()
    {
        if ($this->isDirty('thumb')) {
            $originalProduct = static::find($this->id);
            if ($originalProduct) {
                $originalProduct->deleteImage();
            }
        }
    }

    private function cleanupStripeResources()
    {
        $stripe = new StripeService();

        // Desactivar precios
        foreach (self::$prices as $priceField) {
            $stripePriceIdField = 'stripe_' . $priceField . '_id';
            if ($this->$stripePriceIdField) {
                $this->deactivateStripePriceInternal($stripe, $this->$stripePriceIdField);
            }
        }

        // Desactivar producto
        try {
            $stripe->deactivateProduct($this->stripe_product_id);
        } catch (\Exception $e) {
            Log::warning("Error al desactivar producto de Stripe: {$this->stripe_product_id}", ['error' => $e->getMessage()]);
        }
    }

    private function getStripeImageArray()
    {
        if (config('app.debug')) {
            return [];
        }

        return $this->thumb ? [config('app.url') . $this->thumb] : [];
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

    public function formatApiList()
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'stock' => $this->stock,
            'cover' => config('app.url') . '/storage/' . $this->thumb,
            'price' => $this->price / 100,
            'price_wholesale' => $this->price_wholesale / 100,
            'price_basic_plan' => $this->price_basic_plan / 100,
            'price_premium_plan' => $this->price_premium_plan / 100,
            'detail' => config('app.url') . '/api/products/' . $this->slug
        ];
    }

    public function formatApiDetail()
    {
        $images = [
            [
                'alt' => $this->title,
                'src' => config('app.url') . '/storage/' . $this->thumb
            ]
        ];


        $othersImages = $this->images()->get()->map(fn($record) => [
            "src" => config("app.url") . "/storage/" . $record->path,
            "alt" => $record->alt,
        ]);

        $images = array_merge($images, $othersImages->toArray() ?? []);

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

    public function getCustomerPrice ($suscriptionId, $totalQuantityProducts = 1) {
        // $totalQuantityProducts es la suma de todos los quantity de los productos del carrito
        if($suscriptionId > 1) {
            $prices = [
                2 => "price_basic_plan",
                3 => "price_premium_plan",
            ];
            return [
                "amount" => $this->$prices[$suscriptionId],
                "price_id" => $this->$prices[$suscriptionId] . "_id"
            ];
        }else{
            $isWholesale = $totalQuantityProducts >= 10;
            return [
                "amount" =>  $isWholesale ? $this->price_wholesale : $this->price,
                "price_id" =>  $isWholesale ? $this->price_wholesale_id : $this->price_id,
            ];
        }
    }

}
