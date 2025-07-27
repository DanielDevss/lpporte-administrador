<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "suscription" => ["required_without", "numeric", "exists:suscriptions,id"],
            "products" => ["required_without", "array", "min:1"],
            "products.*.id" => ["required_with", "numeric", "exists:products,id"],
            "products.*.quantity" => ["required_with", "numeric", "min:1"],
        ];
    }

    public function attributes(){
        return [
            'suscription' => 'suscripción',
            'products' => 'productos',
            'products.*' => 'producto',
            'products.*.id' => 'id del producto',
            'products.*.quantity' => 'cantidad del producto'
        ];
    }
}
