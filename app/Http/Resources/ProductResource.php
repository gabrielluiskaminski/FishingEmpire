<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'stock'       => $this->stock,
            'price'       => $this->price,
            'image_path'  => $this->image_path,
            'category'    => $this->whenLoaded('category', fn($category) => [
                'id'      => $category->id,
                'name'    => $category->name,
            ]),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
