<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressStoreRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index() {
        $customer = Auth::user()->customer;
        $addresses = Address::where("customer_id", $customer->id)->get();
        return response()->json($addresses);
    }

    public function store(AddressStoreRequest $request) {
        $data = $request->all();
        $data["customer_id"] = Auth::user()->customer->id;
        $result = Address::create($data);
        return response()->json($result);
    }

    public function update (AddressUpdateRequest $request, string $id) {
        $address = Address::findOrFail($id);
        $result = $address->update($request->all());
        return response()->json($result);
    }

    public function delete (string $id) {
        $address = Address::findOrFail($id);
        $result = $address->delete();
        return response()->json($result);
    }

}
