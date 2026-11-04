<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function index(IndexProductRequest $request, ProductService $productService)
    {
        $products = $productService->listProducts($request->validated());

        return ProductResource::collection($products);
    }

    public function show(int $id, ProductService $productService): ProductResource
    {
        $product = $productService->findById($id);

        return new ProductResource($product);
    }
}
