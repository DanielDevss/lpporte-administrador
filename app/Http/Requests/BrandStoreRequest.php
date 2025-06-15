<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BrandStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:95', 'unique:brands,name'],
            'brand' => ['required', 'image', 'mimes:png,jpg,jpeg']
        ];
    }

    public function attributes() {
        return [
            'name' => 'marca',
            'brand' => 'logotipo'
        ];
    }
}
