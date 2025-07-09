<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distribuitor;
use Illuminate\Http\Request;

class DistribuitorController extends Controller
{
    public function index() {
        $distribuidor = Distribuitor::get()->map(fn ($model) => [
            'id' => $model->id,
            'name' => $model->name,
            'phone' => $model->phone,
            'address' => $model->address,
            'photo' => config('app.url') . '/storage/' . $model->photo
        ]);
        return response()->json($distribuidor);
    }
}
