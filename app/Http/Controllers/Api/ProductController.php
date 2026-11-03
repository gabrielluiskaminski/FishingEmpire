<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true);
        $categoryId = $request->query('category_id');

        if ($request->filled('search')) {
            $search = $request->query('search');

            $query->where('name', 'like', '%{$search}%');
        }

        if ($request->filled('category_id') && is_int($categoryId)) {
            $categoryId = $request->query('category_id');

            $query->where('category_id' == $categoryId);
        } else {
            // algum erro? :P
        }

        if ($request->filled('sort')) {
            $sort = $request->query('sort');
            [$part1, $part2] = explode('-', $sort, 2);

            $query->orderBy($part1, $part2);
        }

        $products = $query->paginate(12)->withQueryString();

        return response()->json($products);
    }
}
