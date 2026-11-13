<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\AdminCatalogService;

class AdminCategoryController extends Controller
{
    public function store(StoreCategoryRequest $request, AdminCatalogService $catalog)
    {
        return (new CategoryResource($catalog->createCategory($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, AdminCatalogService $catalog, int $id)
    {
        return new CategoryResource($catalog->updateCategory($id, $request->validated()));
    }

    public function destroy(AdminCatalogService $catalog, int $id)
    {
        $catalog->deleteCategory($id);
        return response()->noContent();
    }
}
