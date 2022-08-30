<?php


namespace App\Http\Controllers\Frontend;

use App\Models\Store;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Ad\Entities\AdGallery;
use App\Http\Controllers\Controller;
use Modules\Ad\Entities\Ad;
use Modules\Brand\Entities\Brand;
use Modules\Category\Entities\Category;

class StoreController extends Controller
{ 
    public function index()
    {
        $companies = Company::get();
        $brands = Brand::get();
        $store = Store::where('user_id', auth()->user()->id)->first();
        // dd($store);
        return view('frontend.store.index', compact('store', 'brands', 'companies'));
    }

    public function updateStore(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required',
            'logo' => 'required',
            'banner' => 'required',
        ]);
        
        Store::updateOrCreate([
            'user_id' => auth()->user()->id,
        ],[
            'name' => $request->name,
            'logo' => uploadImage($request->logo, 'store'), 
            'banner' => uploadImage($request->banner, 'store'),
            'brand_id' => $request->brand,
            'company_id' => $request->company,
            'address' => $request->address,
        ]);

        return redirect()->route('frontend.store')->withSuccess('store updated successfully');
    }

    public function allStores()
    {
        $stores = Store::latest()->paginate();
        $ad_lists = Ad::latest()->get();

        return view('frontend.store.lists', compact('stores','ad_lists'));
    }

    public function userStoreAds(User $userStore)
    {
        $store = Store::where('user_id', $userStore->id)->first();

        // dd($userStoreAds);
        return view('frontend.user-store-ad-lists', compact('userStore','store'));
    }
}
