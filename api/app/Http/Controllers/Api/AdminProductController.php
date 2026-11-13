<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\AdminCatalogService;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        return ProductResource::collection(
            \App\Models\Product::with('category:id,name')->latest('id')->paginate(12)
        );
    }

    public function store(StoreProductRequest $request, AdminCatalogService $catalog)
    {
        $product = $catalog->createProduct($request->validated(), $request->file('image'));
        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, AdminCatalogService $catalog, int $id)
    {
        return new ProductResource($catalog->updateProduct($id, $request->validated(), $request->file('image')));
    }

    public function destroy(AdminCatalogService $catalog, int $id)
    {
        $catalog->disableProduct($id);
        return response()->json(['message' => 'Produto desativado (histórico preservado).']);
    }
}
