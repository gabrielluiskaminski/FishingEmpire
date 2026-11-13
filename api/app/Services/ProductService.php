<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function listProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::query()->where('is_active', true)->with('category:id,name');
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        [$column, $direction] = match ($filters['sort'] ?? null) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'recent' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            default => ['id', 'asc'],
        };
        $query->orderBy($column, $direction);
        if ($column !== 'id') $query->orderBy('id', 'asc');
        return $query->paginate(12)->appends($filters);
    }

    public function findProductById(int $id): Product
    {
        return Product::where('is_active', true)->with('category:id,name')->findOrFail($id);
    }
}
