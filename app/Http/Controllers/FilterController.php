<?php

namespace App\Http\Controllers;

use Modules\Category\Entities\SubCategory;
use Modules\CustomField\Entities\CustomField;
use Modules\CustomField\Entities\CustomFieldValue;
use Illuminate\Support\Str;
use Modules\Ad\Entities\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Category\Entities\Category;

class FilterController extends Controller
{
    /**
     * Search & filter post by keyword, category
     *
     * @param Request $request
     * @return void
     */
    public function search(Request $request)
    {
        $query = Ad::select('id', 'title', 'slug', 'user_id', 'category_id', 'subcategory_id', 'price', 'thumbnail', 'country', 'region')->activeCategory()->with(['category:id,name', 'productCustomFields' => function ($q) {
            $q->select('id', 'ad_id', 'custom_field_id', 'value', 'order')->with(['customField' => function ($q) {
                $q->select('id', 'name', 'type', 'icon', 'order', 'listable')
                    ->where('listable', 1)
                    ->oldest('order')
                    ->without('customFieldGroup');
            }])->latest();
        }])->active();

        $inputFields = [];
        if (request()->filled('cf')) {
            $inputFields = request()->get('cf');
        }

        foreach ($inputFields as $fieldId => $postValue) {
            $field = CustomField::find($fieldId);
            if (empty($field)) {
                continue;
            }

            if (!is_array($postValue)) {
                // Other fields
                if (trim($postValue) == '') {
                    continue;
                }

                $query->whereHas('productCustomFields', function ($query) use ($field, $postValue) {
                    $query->where('custom_field_id', $field->id)->where('value', 'LIKE', "%$postValue%");
                });
            } else {
                foreach ($postValue as $optionValue) {

                    if (is_array($optionValue)) continue;
                    if (!is_array($optionValue) && trim($optionValue) == '') continue;

                    $query->whereHas('productCustomFields', function ($query) use ($field, $optionValue) {
                        $query->where('custom_field_id', $field->id)->where('value', 'LIKE', "%$optionValue%");
                    });
                }
            }
        }

        if ($request->has('category') && $request->category != null) {
            $category = $request->category;

            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        if ($request->has('subcategory') && $request->subcategory != null) {
            $subcategory = $request->subcategory;

            $query->whereHas('subcategory', function ($q) use ($subcategory) {
                $q->whereIn('slug', $subcategory);
            });
        }

        if ($request->has('category') || $request->has('subcategory')) {
            if ($request->category) {
                $category = Category::where('slug', $request->category)->first();

                if ($category) {
                    $data['searchable_fields'] = $category->customFields->where('filterable', 1)->map(function ($field) {
                        $field->values = CustomFieldValue::where('custom_field_id', $field->id)->get();
                        return $field;
                    });
                } else {
                    $data['searchable_fields'] = [];
                }
            } else {
                $category = SubCategory::where('slug', $request->subcategory)->first()->category;

                if ($category) {
                    $data['searchable_fields'] = $category->customFields->where('filterable', 1)->map(function ($field) {
                        $field->values = CustomFieldValue::where('custom_field_id', $field->id)->get();
                        return $field;
                    });
                } else {
                    $data['searchable_fields'] = [];
                }
            }
        }

        if ($request->has('keyword') && $request->keyword != null) {
            $query->where('title', 'LIKE', "%$request->keyword%");
        }

        // location
        if ($request->has('lat') && $request->has('long') && $request->lat != null && $request->long != null) {
            $ids = $this->location_filter($request->lat, $request->long);

            $query->whereIn('id', $ids);
        }


        if ($request->has('price_min') && $request->price_min != null) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max != null) {
            $query->where('price', '<=', $request->price_max);
        }

        $data['adlistings'] =  $query->paginate(request('per_page', 15))->withQueryString();

        // return $data;
        $data['categories'] = Category::active()->with('subcategories', function ($q) {
            $q->where('status', 1);
        })->latest('id')->get();
        $data['adMaxPrice'] = $price = DB::table('ads')->max('price');
        // return $data;
        return view('frontend.ad-list', $data);
    }

    public function location_filter($latitude, $longitude)
    {
        // $latitude = -58.7699;
        // $longitude = 40.283499;
        $distance = 50;

        $haversine = "(
                    6371 * acos(
                        cos(radians(" . $latitude . "))
                        * cos(radians(`lat`))
                        * cos(radians(`long`) - radians(" . $longitude . "))
                        + sin(radians(" . $latitude . ")) * sin(radians(`lat`))
                    )
                )";

        $data = Ad::select('id')->selectRaw("$haversine AS distance")
            ->having("distance", "<=", $distance)->get();

        $ids = [];

        foreach ($data as $id) {
            array_push($ids, $id->id);
        }

        return $ids;
    }
}
