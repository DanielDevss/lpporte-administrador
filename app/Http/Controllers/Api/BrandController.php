<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index() {

        $brand = Brand::all()->map(fn ($brand) => [
            "id" => $brand->id,
            "name" => $brand->name,
            "img" => config('app.url') . '/storage/' . $brand->brand,
        ]);

        return response()->json($brand, 200);
    }
}
