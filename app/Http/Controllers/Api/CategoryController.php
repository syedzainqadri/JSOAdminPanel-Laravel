<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Category\Entities\Category;
use Modules\Category\Transformers\CategoryResource;
use Modules\Category\Transformers\SubCategoryResource;

class CategoryController extends Controller
{
    public function categories(Request $request)
    {
        $paginate = $request->paginate ?? false;
        $subcategories = $request->subcategories ?? false;

        if ($paginate) {
            $categories = Category::active()
            ->withCount('ads as ad_count')
            ->latest('ad_count')
            ->simplePaginate($paginate);

            $subcategories ? $categories->load('subcategories'): '';
        }else{
            $categories = Category::active()
            ->withCount('ads as ad_count')
            ->latest('ad_count')
            ->get();

            $subcategories ? $categories->load('subcategories'): '';
        }

        return response()->json(CategoryResource::collection($categories));
    }

    public function categoriesSubcategories(Category $category)
    {
        $subcategory = $category->subcategories()->get();

        return SubCategoryResource::collection($subcategory);
    }
}
