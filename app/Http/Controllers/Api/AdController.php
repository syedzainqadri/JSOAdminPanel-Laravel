<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Modules\Ad\Entities\Ad;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\AdCreateTrait;
use Illuminate\Support\Facades\DB;
use Modules\Ad\Entities\AdGallery;
use App\Http\Controllers\Controller;
use Modules\Ad\Actions\DeleteAdGallery;
use Modules\Ad\Transformers\AdResource;
use Modules\Category\Entities\Category;
use Modules\Ad\Transformers\AdResourceMobile;
use App\Http\Requests\Api\AdCreateFormRequest;
use App\Http\Requests\Api\AdUpdateFormRequest;
use Modules\Ad\Transformers\AdDetailsResource;
use Modules\Ad\Transformers\AdResourcePaginate;

class AdController extends Controller
{
    use AdCreateTrait;

    public function categoryWiseAds(Category $category)
    {
        $paginate = request()->paginate ?? false;

        $category_wise_ads = $category->ads()->with('category');

        if ($paginate) {
            $ads = $category_wise_ads->simplePaginate($paginate);
        } else {
            $ads = $category_wise_ads->get();
        }

        return AdResourceMobile::collection($ads);
    }

    public function adDetails(Ad $ad)
    {
        $ad_details = new AdDetailsResource($ad->load('category', 'subcategory', 'customer', 'brand', 'adFeatures', 'galleries', 'city', 'town')->loadCount(['wishlists as wishlisted' => function ($q) {
            $q->where('user_id',  auth('api')->id());
        }]));
        $related_ads = AdResourceMobile::collection(Ad::with('city')->whereCategoryId($ad->category_id)->where('id', '!=', $ad->id)->latest()->limit(4)->get());

        return [
            'ad_details' => $ad_details,
            'related_ads' => $related_ads,
        ];
    }

    public function adsCollection(Request $request)
    {
        $paginate = $request->paginate ?? 10;
        $filter_by = $request->filter_by ?? false;
        $sort_by = $request->sort_by ?? false;
        $query = Ad::with('city', 'category')->active();

        // Category filter
        if ($request->has('category') && $request->category != null) {
            $category = $request->category;

            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        // Subcategory filter
        if ($request->has('subcategory') && $request->subcategory != null) {
            $subcategory = $request->subcategory;

            $query->whereHas('subcategory', function ($q) use ($subcategory) {
                $q->whereIn('slug', $subcategory);
            });
        }

        // Keyword search
        if ($request->has('keyword') && $request->keyword != null) {
            $query->where('title', 'LIKE', "%$request->keyword%");
        }

        // City filter
        if ($request->has('city') && $request->city != null) {
            $city = $request->city;

            $query->whereHas('city', function ($q) use ($city) {
                $q->whereIn('name', $city);
            });
        }

        // Town filter
        if ($request->has('town') && $request->town != null) {
            $query->whereHas('town', function ($q) {
                $q->where('name', request('town'));
            });
        }

        // Price filter
        if ($request->has('price_min') && $request->price_min != null) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max != null) {
            $query->where('price', '<=', $request->price_max);
        }

        // Filter by ads
        if ($filter_by && $filter_by == 'featured') {
            $query->where('featured', 1);
        } else if ($filter_by && $filter_by == 'popular') {
            $query->latest('total_views');
        }

        // Sort by ads
        if ($sort_by && $sort_by == 'latest') {
            $query->latest();
        } else if ($sort_by && $sort_by == 'oldest') {
            $query->oldest();
        }

        return [
            'ads' => $query->paginate($paginate)->withQueryString(),
            'adMaxPrice' => \DB::table('ads')->max('price'),
        ];
    }

    public function storeAd(AdCreateFormRequest $request)
    {
        $limit_exists = $this->adLimitChecking();
        $featured_exists = $this->featuredAdChecking();
        $maximum_ad_image_limit = setting('maximum_ad_image_limit');
        $base64 = $request->base64 ?? true;
        $image_count = $base64 ? count($request->images) : count($request->file('images'));

        try {
            if (!$limit_exists) {
                return response()->json([
                    'message' => 'You have reached your ad limit. Please upgrade your plan to add more ads.',
                ], Response::HTTP_FORBIDDEN);
            }

            if ($request->featured && !$featured_exists) {
                return response()->json([
                    'message' => 'You have reached your featured ad limit. Please upgrade your plan to add more featured ads.',
                ], Response::HTTP_FORBIDDEN);
            }


            if (($image_count < 2) || ($image_count > $maximum_ad_image_limit)) {
                return response()->json([
                    'message' => 'Please upload at least 2 to ' . $maximum_ad_image_limit . 'images.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $ad = new Ad();
            $ad->title = $request->title;
            $ad->slug = Str::slug($request->title);
            $ad->user_id = auth('api')->id();
            $ad->category_id = $request->category_id;
            $ad->subcategory_id = $request->subcategory_id;
            $ad->brand_id = $request->brand_id;
            $ad->city_id = $request->city_id;
            $ad->town_id = $request->town_id;
            $ad->price = $request->price;
            $ad->phone = $request->phone;
            $ad->phone_2 = $request->phone_2;
            $ad->featured = $request->featured ? $request->featured : 0;
            $ad->description = $request->description;
            $ad->status = setting('ads_admin_approval') ? 'pending' : 'active';

            $ad->save();

            // image inserting
            if ($base64 && $request->images) {
                $images = $request->images;


                foreach ($images as $key => $image) {
                    if ($key == 0 && $image) {
                        $url = uploadBase64FileToPublic($image, 'uploads/addds_images/');
                        $ad->update(['thumbnail' => $url]);
                    }

                    if ($image) {
                        $gallery_url = uploadBase64FileToPublic($image, 'uploads/adds_multiple/');
                        $ad->galleries()->create(['image' => $gallery_url]);
                    }
                }
            } else {
                $images = $request->file('images');

                if ($images) {
                    foreach ($images as $key => $image) {
                        if ($key == 0 && $image && $image->isValid()) {
                            $url = $image->move('uploads/addds_images', $image->hashName());
                            $ad->update(['thumbnail' => $url]);
                        }

                        if ($image && $image->isValid()) {
                            $gallery_url = $image->move('uploads/adds_multiple', $image->hashName());
                            $ad->galleries()->create(['image' => $gallery_url]);
                        }
                    }
                }
            }

            // feature inserting
            if ($request->features) {
                foreach ($request->features as $feature) {
                    if ($feature) {
                        $ad->adFeatures()->create(['name' => $feature]);
                    }
                }
            }

            $this->adNotification($ad, 'create', true);
            $this->userPlanInfoUpdate($ad->featured, auth('api')->id());

            return response()->json([
                'success' => true,
                'message' => 'Ad created successfully',
                'ad' => $ad->load('category', 'customer', 'brand', 'adFeatures', 'galleries', 'city', 'town')
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function editAd(Ad $ad)
    {
        if ($ad->user_id != auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to do this action'
            ], Response::HTTP_FORBIDDEN);
        }

        return $ad->load('category', 'subcategory', 'customer', 'brand', 'adFeatures', 'galleries', 'city', 'town');
    }

    public function updateAd(AdUpdateFormRequest $request, Ad $ad)
    {
        if ($ad->user_id != auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to do this action'
            ], Response::HTTP_FORBIDDEN);
        }

        $ad->title = $request->title;
        $ad->slug = Str::slug($request->title);
        $ad->category_id = $request->category_id;
        $ad->subcategory_id = $request->subcategory_id;
        $ad->brand_id = $request->brand_id;
        $ad->city_id = $request->city_id;
        $ad->town_id = $request->town_id;
        $ad->price = $request->price;
        $ad->phone = $request->phone;
        $ad->phone_2 = $request->phone_2;
        $ad->featured = $request->featured ? $request->featured : 0;
        $ad->description = $request->description;
        $ad->save();

        // image updating
        $base64 = $request->base64 ?? true;

        if ($base64 && $request->images) {
            // delete old images
            $ad->galleries()->delete();

            $images = $request->images;

            foreach ($images as $image) {
                $gallery_url = uploadBase64FileToPublic($image, 'uploads/adds_multiple/');
                $ad->galleries()->create(['image' => $gallery_url]);
            }
        } else {
            // delete old images
            $ad->galleries()->delete();

            $images = $request->file('images');
            if ($images) {
                foreach ($images as $image) {
                    if ($image && $image->isValid()) {
                        $gallery_url = $image->move('uploads/adds_multiple', $image->hashName());
                        $ad->galleries()->create(['image' => $gallery_url]);
                    }
                }
            }
        }

        if ($base64 && $request->thumbnail) {
            $thumbnail = $request->thumbnail;
            deleteImage($ad->thumbnail);
            $url = uploadBase64FileToPublic($thumbnail, 'uploads/addds_images/');
            $ad->update(['thumbnail' => $url]);
        } else {
            $thumbnail = $request->file('thumbnail');
            if ($thumbnail && $thumbnail->isValid()) {
                deleteImage($ad->thumbnail);
                $url = $thumbnail->move('uploads/addds_images', $thumbnail->hashName());
                $ad->update(['thumbnail' => $url]);
            }
        }

        // feature inserting
        $ad->adFeatures()->delete();
        foreach ($request->features as $feature) {
            if ($feature) {
                $ad->adFeatures()->create(['name' => $feature]);
            }
        }

        $this->adNotification($ad, 'update', true);

        return response()->json([
            'success' => true,
            'message' => 'Ad updated successfully',
            'ad' => $ad->load('category', 'customer', 'brand', 'adFeatures', 'galleries', 'city', 'town')
        ], Response::HTTP_OK);
    }

    public function deleteAdGallery($ad_gallery)
    {
        $ad_gallery = AdGallery::find($ad_gallery);

        if ($ad_gallery) {
            $ad_gallery->delete();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ad gallery image deleted successfully',
        ], Response::HTTP_OK);
    }
}
