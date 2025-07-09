<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Suscription;
use Illuminate\Http\Request;

class SuscriptionController extends Controller
{
    public function index() {
        return Suscription::select(['id', 'name', 'amount', 'benefits', 'attributes'])
            ->get()
            ->map(fn ($sub) => $sub->formatApiList());
    }
}
