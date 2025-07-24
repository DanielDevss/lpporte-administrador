<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddressStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'cp' => ['required', 'numeric', 'min:10000', 'max:99999'],
            'state' => ['required', 'string'],
            'col' => ['required', 'string', 'max:125', 'min:3'],
            'city' => ['required', 'string', 'max:125', 'min:3'],
            'street' => ['required', 'string', 'max:155', 'min:3'],
            'no_ext' => ['required', 'string', 'max:10', 'min:1'],
            'no_int' => ['nullable', 'string', 'max:10', 'min:1'],
            'ref_address' => ['nullable', 'string', 'max:250'],
            'street_ref_1' => ['nullable', 'string', 'max:125', 'min:3'],
            'street_ref_2' => ['nullable', 'string', 'max:125', 'min:3'],
            'street_ref_3' => ['nullable', 'string', 'max:125', 'min:3'],
            'street_ref_4' => ['nullable', 'string', 'max:125', 'min:3'],
            'main' => ['nullable','boolean']
        ];
    }
}
