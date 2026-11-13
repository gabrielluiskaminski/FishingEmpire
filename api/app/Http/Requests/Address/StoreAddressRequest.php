<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:255'],
            'house_number' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'cep' => ['required', 'digits:8'],
        ];
    }
}
