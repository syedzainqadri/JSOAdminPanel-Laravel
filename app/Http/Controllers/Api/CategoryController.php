<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Category\Entities\Category;
use Modules\Category\Transformers\CategoryResource;
use Modules\Category\Transformers\SubCategoryResource;
use Illuminate\Support\Str;
use Validator;

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

    public function create_category(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }
        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->slug);
        $data['description'] = $request->description;
        $data['meta_title'] = $request->meta_title;
        $data['meta_description'] = $request->meta_description;
        $data['meta_keywords'] = $request->meta_keywords;
        $data['status'] = $request->status;

        $category = Category::create($data);
        return $category;
    }

    public function update_category(Request $request, $cat_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }
        $category = Category::find($cat_id);
        if (!$category) {
            return response()->json('Category not found');
        }
        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->slug);
        $data['description'] = $request->description;
        $data['meta_title'] = $request->meta_title;
        $data['meta_description'] = $request->meta_description;
        $data['meta_keywords'] = $request->meta_keywords;
        $data['status'] = $request->status;
        $category -> update($data);
        return $category;
    }

    public function show_category(Request $request, $cat_id)
    {
        $category = Category::findOrFail($cat_id);
        return $category;
    }

    public function delete_category(Request $request, $cat_id)
    {
        $category = Category::where('id', $cat_id)->delete();
        return $category ? response()->json('Category deleted successfully') : $category;
    }
}
