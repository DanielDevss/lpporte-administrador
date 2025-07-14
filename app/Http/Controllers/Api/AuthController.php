<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountCustomerStoreRequest;
use App\Http\Requests\AuthSignInRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /**
     * Iniciar sesión
     */

    public function signIn(AuthSignInRequest $request)
    {
        $user = User::with('customer')
            ->where('email', $request->input('email'))
            ->first();

        // Verificar que el usuario tenga customer y la contraseña sea correcta
        if (!$user || !$user->customer || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas']
            ]);
        }

        // Retornar el token de acceso
        return response()->json([
            'token' => $user->createToken('token-api')->plainTextToken
        ]);
    }

    /** 
     * Crear una cuenta
     */

    public function signUp (AccountCustomerStoreRequest $request) {
        try {

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            Customer::create([
                'user_id' => $user->id,
                'suscription_id' => 1
            ]);
            
            Log::info("Se ha registrado un nuevo usuario cliente con id: {$user->id}");

            DB::commit();

            $token = $user->createToken('token-api')->plainTextToken;

            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error al registrar una cuenta:' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cerrar sesión
     */

    public function signOut(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }

    /**
     * Comprobar sesión
     */

    public function verifyAuth () {

        return response()->json([
            'message' => 'La sesión está activa'
        ], 200);
    }    
}
