<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all()->map(fn ($category) => [
            "id" => $category->id,
            "name" => $category->name,
            "slug" => $category->slug,
            "products" => config('app.url') . "/api/{$category->id}/products"
        ]);

        return response()->json($categories, 200);
    }
}
