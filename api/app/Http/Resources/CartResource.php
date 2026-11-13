<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $total = 0;
        $items = $this->cartItems->map(function ($item) use (&$total) {
            $product = $item->product;
            $unit = Money::cents((string) $product->price);
            $subtotal = $unit * $item->quantity;
            $total += $subtotal;

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $item->quantity,
                'stock' => $product->stock,
                'is_active' => (bool) $product->is_active,
                'available' => (bool) $product->is_active && $product->stock >= $item->quantity,
                'image_path' => $product->image_path,
                'unit_price' => Money::format($unit),
                'subtotal' => Money::format($subtotal),
            ];
        });
        return ['id' => $this->id, 'items' => $items, 'total' => Money::format($total)];
    }
}
