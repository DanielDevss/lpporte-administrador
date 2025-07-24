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
            "products" => ["required", "array", "min:1"],
            "products.*.id" => ["required", "numeric", "exists:products,id"],
            "products.*.quantity" => ["required", "numeric", "min:1"]
        ];
    }

    public function attributes(){
        return [
            'products' => 'productos',
            'products.*' => 'producto',
            'products.*.id' => 'id del producto',
            'products.*.quantity' => 'cantidad del producto'
        ];
    }
}
