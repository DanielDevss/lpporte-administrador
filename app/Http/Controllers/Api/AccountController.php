<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $user->load('customer');

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'referance_code' => $user->customer->reference_code,
                'suscription_id' => $user->customer->suscription_id,
                'suscription_active' => $user->customer->suscription_active,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }
}
