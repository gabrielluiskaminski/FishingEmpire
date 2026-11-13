<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AdminCatalogService
{
    public function createProduct(array $data, ?UploadedFile $image = null): Product
    {
        if ($image) $data['image_path'] = $image->store('products', 'public');
        unset($data['image']);
        $data['is_active'] ??= true;
        return Product::create($data)->load('category:id,name');
    }

    public function updateProduct(int $id, array $data, ?UploadedFile $image = null): Product
    {
        $product = Product::findOrFail($id);
        if ($image) $data['image_path'] = $image->store('products', 'public');
        unset($data['image']);
        $product->update($data);
        return $product->load('category:id,name');
    }

    public function disableProduct(int $id): Product
    {
        // Desativa em vez de apagar fisicamente: mantém pedidos históricos e FKs intactos.
        $product = Product::findOrFail($id);
        $product->update(['is_active' => false]);
        return $product;
    }

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function updateCategory(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function deleteCategory(int $id): void
    {
        DB::transaction(function () use ($id) {
            $category = Category::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($category->products()->exists()) {
                abort(409, 'Categoria vinculada a produtos.');
            }
            $category->delete();
        });
    }
}
