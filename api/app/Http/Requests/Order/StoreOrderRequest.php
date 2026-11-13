<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'address_id' => [
                'required', 'integer',
                Rule::exists('addresses', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'Selecione um endereço para finalizar o pedido.',
            'address_id.exists' => 'Endereço não encontrado ou não pertence à sua conta.',
        ];
    }
}
