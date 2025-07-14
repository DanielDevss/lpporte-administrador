<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index() {
        $products = Product::publics()
            ->publics()
            ->get()
            ->map(fn ($product) => $product->formatApiList());
        return $products;
    }

    public function byCategory(string $category_id) {
        $products = Category::findOrFail($category_id)
            ?->products()
            ?->publics()
            ?->get()
            ?->map(fn($product) => $product->formatApiList());
        return $products ?? [];
    }

    public function show(string $slug) {

        $product = Product::with('images')
            ->publics()
            ->where('slug', $slug)
            ->firstOrFail()?->formatApiDetail();

        return $product;
    }
}
