<?php

namespace App\Http\Controllers\Api;

use App\Models\Cms;
use App\Models\Setting;
use App\Models\User;
use Modules\Ad\Entities\Ad;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Faq\Entities\Faq;
use Modules\Plan\Entities\Plan;
use Modules\Brand\Entities\Brand;
use Modules\Location\Entities\City;
use Modules\Location\Entities\Town;
use App\Http\Controllers\Controller;
use App\Http\Resources\FaqsResource;
use App\Http\Resources\PlanResource;
use Modules\Contact\Entities\Contact;
use Modules\Faq\Entities\FaqCategory;
use Modules\Faq\Transformers\FaqResource;
use App\Http\Resources\FaqCategoriesResource;
use Modules\Brand\Transformers\BrandResource;
use Modules\Testimonial\Entities\Testimonial;
use Modules\Location\Transformers\CityResource;
use Modules\Location\Transformers\TownResource;
use Modules\Faq\Transformers\FaqCategoryResource;

class AppController extends Controller
{
    public function testimonialList()
    {

        return Testimonial::latest('id')->get();
    }

    public function cities(Request $request)
    {
        $paginate = $request->paginate ?? false;

        if ($paginate) {
            return CityResource::collection(City::withCount('ads')->latest('ads_count')->simplePaginate($paginate));
        } else {
            return CityResource::collection(City::withCount('ads')->latest('ads_count')->get());
        }

        $cities = City::latest()->paginate(10);

        return response()->json($cities);
    }

    public function citiesTowns(City $city)
    {
        $towns = $city->towns()->get();

        return TownResource::collection($towns);
    }

    public function contactMessage(Request $request)
    {
        $request->validate([
            'name' => 'required|max:120',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required|min:10',
        ]);

        $contact = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        if ($contact) {
            return response()->json([
                'success' => true,
                'message' => 'Message Send Successfully',
                'data' => $contact
            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function faqsCategory()
    {
        $faqs_categories = FaqCategory::latest()->get();

        return FaqCategoryResource::collection($faqs_categories);
    }

    public function categoriesFaq(FaqCategory $category)
    {
        $faqs = Faq::where('faq_category_id', $category->id)->latest()->get();

        return FaqResource::collection($faqs);
    }

    public function contactContent()
    {
        $contactData = Setting::select('phone', 'email', 'address')->first();

        if ($contactData) {

            return response()->json([
                'success' => true,
                'message' => 'Data fetch success',
                'data' => $contactData,

            ], Response::HTTP_OK);
        } else {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function postingrulesContent()
    {

        $posting_rules = Cms::select("posting_rules_background", "posting_rules_body")->first();

        if ($posting_rules) {

            return response()->json([
                'success' => true,
                'message' => 'Data fetch success',
                'data' => $posting_rules,

            ], Response::HTTP_OK);
        } else {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function aboutContent()
    {
        $about = Cms::select("about_video_thumb", "about_background", "about_body")->first();
        $about = Cms::select("about_video_thumb", "about_background", "about_body")->first();
        $cities = City::count();
        $towns = Town::count();
        $verified_users = User::where('email_verified_at', '!=', null)->count();
        $published_ads = Ad::activeCategory()->active()->count();

        if ($about) {
            return response()->json([
                'success' => true,
                'message' => 'Data fetch success',
                'data' => [
                    'about_content' => $about,
                    'published_ads_count' => $published_ads,
                    'verified_users_count' => $verified_users,
                    'cities_count' => $cities,
                    'towns_count' => $towns,
                    'total_cities_towns_count' => $cities + $towns,
                ],

            ], Response::HTTP_OK);
        } else {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function brands()
    {
        $brands = Brand::latest()->get();

        return BrandResource::collection($brands);
    }

    public function planList()
    {
        $plans =  Plan::all();

        return PlanResource::collection($plans);
    }

    public function generateToken(Request $request)
    {
        try {
            auth('api')->user()->update(['fcm_token' => $request->token]);

            return response()->json([
                'token' => $request->token,
                'message' => 'Token Generated Successfully',
                'user' => auth('api')->user(),
                'success' => true
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false
            ], 500);
        }
    }
}
