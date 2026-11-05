<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function listCategories()
    {
        return Category::query()->orderBy('name', 'asc')->get();
    }

    public function findCategoryById(int $id): Category 
    {
        return Category::query()->findOrFail($id);
    }
}
