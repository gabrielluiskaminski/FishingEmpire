<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    public function index(CategoryService $categoryService){
        $categories = $categoryService->listCategories();

        return CategoryResource::collection($categories);
    }

    public function show(CategoryService $categoryService, int $id){
        $category = $categoryService->findCategoryById($id);
        return new CategoryResource($category);
    }
}
