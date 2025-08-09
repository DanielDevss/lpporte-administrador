<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressStoreRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    public function index()
    {
        $customer = Auth::user()->customer;
        $addresses = Address::select('id', 'main', 'name', 'street', 'cp', 'no_ext', 'state', 'city')
            ->where("customer_id", $customer->id)
            ->orderBy('main', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($address) {
                $formatAddress = $address->street;
                $formatAddress .= " Ext." . $address->no_ext;
                $formatAddress .= boolval($address->no_int) ? ", Dep." . $address->no_int : ", ";
                $formatAddress .= "{$address->city}, {$address->state}.";
                return [
                    'id' => $address->id,
                    'name' => $address->name,
                    'main' => $address->main,
                    'address' => $formatAddress
                ];
            });

        return response()->json($addresses);
    }

    /** LINK: Agregar
     * Agrega una nueva dirección a la base de datos
     * del usuario
     */

    public function store(AddressStoreRequest $request)
    {
        $user = Auth::user();
        $customerId = $user->customer->id;

        return DB::transaction(function () use ($request, $customerId) {
            $data = $request->all();
            $data['customer_id'] = $customerId;

            if ($request->boolean('main')) {
                // Poner todas las demás como false
                Address::where('customer_id', $customerId)
                    ->update(['main' => false]);

                $data['main'] = true;
            }

            $address = Address::create($data);

            return response()->json($address);
        });
    }

    /** LINK Obtener uno
     * Devuelve el registro con el id recibido
     */

    public function show(string $id)
    {
        $address = Address::select(
            'id',
            'main',
            'name',
            'cp',
            'state',
            'col',
            'street',
            'city',
            'no_ext',
            'no_int',
            'street_ref_1',
            'street_ref_2',
            'street_ref_3',
            'street_ref_4',
            'ref_address'
        )
            ->findOrFail($id);
        return response()->json($address);
    }

    /** LINK Actuliza 
     * Actualiza la dirección del usuario
     */

    public function update(AddressUpdateRequest $request, string $id)
    {
        $address = Address::findOrFail($id);
        $address->update($request->all());
        return response()->json($address);
    }

    /** LINK Elimina
     * Elimina la dirección del usuario solo si no es favorito
     */

    public function delete(string $id)
    {
        $address = Address::where('main', false)
            ->findOrFail($id);
        $result = $address->delete();
        return response()->json($result);
    }


    /** LINK Marcar como principal */

    public function setMain(string $id)
    {
        $user = Auth::user();
        $customerId = $user->customer->id;

        return DB::transaction(function () use ($id, $customerId, $user) {
            // Desmarcar todas (una sola query, sin first())
            Address::where('customer_id', $customerId)->update(['main' => false]);

            // Marcar la seleccionada y validar pertenencia
            $affected = Address::where('customer_id', $customerId)
                ->whereKey($id)
                ->update(['main' => true]);

            if ($affected === 0) {
                // No existe o no pertenece al usuario
                return response()->json([
                    'success' => false,
                    'message' => 'Dirección no encontrada'
                ], 404);
            }

            Log::info('Dirección principal establecida', [
                'customer_id' => $customerId,
                'address_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dirección actualizada'
            ], 200);
        });
    }


}
