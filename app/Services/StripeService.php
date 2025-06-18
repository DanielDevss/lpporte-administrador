<?php
namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripeService {
    public function __construct() {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession (array $options): Session {
        return Session::create($options);
    }

    /**
     * Productos
     */

    public function createProduct(array $options):Product {
        return Product::create($options);
    }

    public function updateProduct(array $options, string $product_id) {
        return Product::update($product_id, $options);
    }

    public function createPrice(array $options):Price {
        return Price::create($options);
    }
}

