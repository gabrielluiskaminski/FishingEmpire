<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function Laravel\Prompts\number;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalCents = 0;

        $items = $this->cartItems->map(function ($item) use (&$totalCents) {
            $product = $item->product;

            $priceCents = (int) round ((float) $product->price * 100);
            $subtotalCents = $priceCents * $item->quantity;

            $totalCents += $subtotalCents;

            return [
                'id'         => $item->id,
                'product_id' => $product->id,
                'name'       => $product->name,
                'quantity'   => $item->quantity,
                'stock'      => $product->stock,
                'image_path' => $product->image_path,
                'unit_price' => number_format($priceCents / 100, 2, '.', ''),
                'subtotal'   => number_format($subtotalCents / 100, 2, '.', ''),
            ];
        });

        return [
            'id'    => $this->id,
            'items' => $items,
            'total' => number_format($totalCents / 100, 2, '.', ''),
        ];
    }
}
