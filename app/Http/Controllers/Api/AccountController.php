<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

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

        $suscriptionName = $user->customer?->suscription?->name ?? "Plan Gratis";
        $addressMainId = $user->customer->addresses()->where('main', true)->first();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'referance_code' => $user->customer->reference_code,
                'suscription_id' => $user->customer->suscription_id,
                'suscription_active' => $user->customer->suscription_active,
                'sucription_name' => $suscriptionName,
                'address_main_id' => $addressMainId ?? null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }

    public function update(AccountUpdateRequest $request)
    {
        try {
            $user = User::find($request->user()->id);
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
            ]);
            return response()->json([
                'message' => 'Cuenta actualizada'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar la cuenta de usuario ' . $request?->user()?->id, [$e->getMessage()]);
            return response()->json([
                'message' => 'No se pudo actualizar la cuenta',
            ], 500);
        }
    }

    public function changePassword(PasswordUpdateRequest $request)
    {
        try {
            $user = $request->user();

            // Verificar que la contraseña actual sea correcta
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta'
                ], 422);
            }

            // Actualizar la contraseña
            $user->update([
                'password' => Hash::make($request->newPassword)
            ]);

            return response()->json([
                'message' => 'Contraseña actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar la contraseña del usuario ' . $request->user()->id, [$e->getMessage()]);
            return response()->json([
                'message' => 'No se pudo cambiar la contraseña',
            ], 500);
        }
    }
}
