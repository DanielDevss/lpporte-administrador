<?php
namespace App\Services;

use Exception;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripeService {
    public function __construct() {
        Stripe::setApiKey(config('services.stripe.secret'));
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

    public function deactivateProduct(string $product_id) {
        return Product::update($product_id, ['active' => false]);
    }

    public function createPrice(array $options):Price {
        return Price::create($options);
    }

    public function updatePrice (array $options, string $price_id) {
        return Price::update($price_id, $options);
    }

    public function deactivatePrice(string $price_id) {
        return Price::update($price_id, ['active' => false]);
    }

    public function getPrice(string $price_id) {
        return Price::retrieve($price_id);
    }

    public function listPrices(array $options = []) {
        return Price::all($options);
    }

    public function createCheckout(array $options = []): Session {
        return Session::create($options);
    }

    public function findSession(string $id) {
        if(!$id) {
            throw new Exception('No existe el session_id');
        }
        return Session::retrieve($id);
    }

    public function findPaymentIntent (string $id) {
        if(!$id) {
            throw new Exception('No existe el payment_intent_id');
        }
        return PaymentIntent::retrieve($id);
    }
}

