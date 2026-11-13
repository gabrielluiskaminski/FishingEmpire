<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'house_number' => ['sometimes', 'required', 'string', 'max:30'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'state' => ['sometimes', 'required', 'string', 'size:2'],
            'cep' => ['sometimes', 'required', 'digits:8'],
        ];
    }
}
