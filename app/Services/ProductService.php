<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Product;

class ProductService
{

    public function listProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::query()->where('is_active', true)->with('category:id,name');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where('name', 'like', "%{$search}%");
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $sortOptions = [
            'price_asc'  => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name_asc'   => ['name', 'asc'],
        ];

        [$column, $direction] = $sortOptions[$filters['sort'] ?? ''] ?? ['id', 'asc'];

        $query->orderBy($column, $direction);

        if ($column !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return $query->paginate(12)->appends($filters);
    }

    public function findProductById(int $id): Product
    {
        return Product::query()->where('is_active', true)->with('category:id,name')->findOrFail($id);
    }
}
