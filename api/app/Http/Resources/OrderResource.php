<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'customer' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'status' => $this->status,
            'total' => Money::format(Money::cents((string) $this->total)),
            'shipping_cost' => Money::format(Money::cents((string) $this->shipping_cost)),
            'shipping' => [
                'address' => $this->shipping_address,
                'number' => $this->shipping_number,
                'cep' => $this->shipping_cep,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
            ],
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'items' => $this->whenLoaded('orderItems', function () {
                return $this->orderItems->map(function ($item) {
                    $unitCents = Money::cents((string) $item->product_price);
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'product_price' => Money::format($unitCents),
                        'subtotal' => Money::format($unitCents * $item->quantity),
                    ];
                });
            }),
        ];
    }
}
