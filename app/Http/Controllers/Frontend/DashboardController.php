<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Models\UserPlan;
use App\Models\Transaction;
use Modules\Ad\Entities\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Actions\Frontend\ProfileUpdate;
use App\Models\Setting;
use Modules\Category\Entities\Category;
use Modules\Wishlist\Entities\Wishlist;
use App\Notifications\AdDeleteNotification;
use App\Notifications\AdWishlistNotification;
use App\Rules\MatchOldPassword;
use Modules\Plan\Entities\Plan;

class DashboardController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        storePlanInformation();
        $auth_user_plan = session('user_plan');
        $authUser = auth('user')->user();
        $ads = Ad::customerData()->get();
        $activities = auth('user')->user()->notifications()->latest()->limit(5)->get();
        $recent_ads = Ad::customerData()->with('category')->latest('id')->get()->take(10);
        $favourite_count = Wishlist::whereUserId($authUser->id)->count();
        $posted_ads_count = $ads->where('user_id', $authUser->id)->count();
        $expire_ads_count = $ads->where('status', 'sold')->where('user_id', $authUser->id)->count();

        // bar chart by year
        $bar_chart_datas = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

        for ($i = 0; $i < 12; $i++) {
            $bar_chart_datas[$i] = (int)Ad::customerData()
                ->select('total_views')
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', $i + 1)
                ->sum('total_views');
        }

        return view('frontend.dashboard', [
            'activities' =>  $activities,
            'recent_ads' =>  $recent_ads,
            'favouriteadcount' =>  $favourite_count,
            'posted_ads_count' =>  $posted_ads_count,
            'expire_ads_count' =>  $expire_ads_count,
            'bar_chart_datas' =>  $bar_chart_datas,
            'user_plan' =>  $auth_user_plan,
        ]);
    }

    public function editAd(Ad $ad)
    {
        $data['user'] = auth()->user();
        $data['ad'] = $ad;
        return view('frontend.edit-ad', $data);
    }

    public function myAds()
    {
        $data['categories'] = Category::active()->get();
        $data['user'] = auth('user')->user();

        $query =  Ad::customerData();

        if (request()->has('keyword') && request()->keyword != null) {
            $keyword = request('keyword');
            $query->where('title', 'LIKE', "%$keyword%");
        }

        if (request()->has('category') && request()->category != null) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', request('category'));
            });
        }

        if (request()->has('sort_by') && request()->sort_by != null && request('sort_by') == 'oldest') {
            $query->orderBy('id', 'ASC');
        } else {
            $query->orderBy('id', 'DESC');
        }

        $data['ads'] = $query->paginate(5)->withQueryString();

        return view('frontend.myad', $data);
    }

    public function deleteMyAd(Ad $ad)
    {
        $ad->delete();

        flashSuccess('One of your ad has deleted');
        $this->addeleteNotification();
        return back();
    }

    public function addeleteNotification()
    {
        // Send ad create notification
        $user = auth('user')->user();
        if (checkSetup('mail')) {
            $user->notify(new AdDeleteNotification($user));
        }
    }

    public function myAdStatus(Ad $ad)
    {
        if ($ad->status == 'active') {
            $ad->status = 'sold';
        } elseif (($ad->status == 'sold')) {
            $ad->status = 'active';
        }
        $ad->update();

        flashSuccess('Status updated successfully!');
        return back();
    }

    public function favourites()
    {
        $wishlistsIds = Wishlist::select('ad_id')
            ->customerData()
            ->pluck('ad_id')
            ->all();


        $query = Ad::select(['id', 'title', 'slug', 'thumbnail', 'price', 'status', 'category_id', 'created_at'])
            ->with('category:id,name')
            ->whereIn('id', $wishlistsIds);

        if (request()->has('keyword') && request()->keyword != null) {
            $keyword = request('keyword');
            $query->where('title', 'LIKE', "%$keyword%");
        }

        if (request()->has('category') && request()->category != null) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', request('category'));
            });
        }

        if (request()->has('sort_by') && request()->sort_by != null && request('sort_by') == 'oldest') {
            $query->orderBy('id', 'ASC');
        } else {
            $query->orderBy('id', 'DESC');
        }

        $data['wishlists'] = $query->paginate(5)->withQueryString();

        return view('frontend.favourite-ads', $data);
    }

    public function message()
    {
        $user['user'] = auth()->user();
        return view('frontend.message', $user);
    }

    public function plansBilling()
    {
        storePlanInformation();
        $data['user_plan'] = session('user_plan');

        if ($data['user_plan']->subscription_type == 'recurring' && $data['user_plan']->current_plan_id) {
            $data['user_plan'] = $data['user_plan'];
            $data['current_plan'] = Plan::find($data['user_plan']->current_plan_id);
        }

        $data['plan_info'] = UserPlan::customerData()->firstOrFail();
        $data['transactions'] = Transaction::with('plan')->customerData()->latest()->get()->take(5);

        return view('frontend.plans-billing', $data);
    }

    public function cancelPlan()
    {
        $user_plan = auth('user')->user()->userPlan;
        $plan = Plan::find($user_plan->current_plan_id);

        $user_plan->update([
            'ad_limit' => $user_plan->ad_limit ? $user_plan->ad_limit - $plan->ad_limit : 0,
            'featured_limit' => $user_plan->featured_limit ? $user_plan->featured_limit - $plan->featured_limit : 0,
            'current_plan_id' => null,
            'expired_date' => null,
        ]);

        flashSuccess('Plan cancelled successfully!');
        return back();
    }

    public function accountSetting()
    {
        $user = auth('user')->user();
        $social_medias = $user->socialMedia;

        return view('frontend.account-setting', compact('user', 'social_medias'));
    }

    public function profileUpdate(Request $request)
    {
        $customer = auth('user')->user();

        $request->validate([
            'name' => "required",
            'email' => "required|email|unique:users,email,{$customer->id}",
            'phone' => "sometimes|nullable",
            'web' => "sometimes|nullable|url",
        ]);

        try {
            $customer = ProfileUpdate::update($request, $customer);

            if ($customer) {
                flashSuccess('Profile Updated Successfully');
                return back();
            }
        } catch (\Exception $e) {
            flashError();
            return back();
        }
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);
        $password_check = Hash::check($request->current_password, auth('user')->user()->password);

        if ($password_check) {
            $user = User::findOrFail(auth('user')->id());
            $user->update([
                'password' => bcrypt($request->password),
                'updated_at' => Carbon::now(),
            ]);

            flashSuccess('Password Updated Successfully');
            return back();
        } else {
            flashError('Something went wrong');
            return back();
        }
    }

    public function socialUpdate(Request $request)
    {
        // return $request;
        $user = auth('user')->user();

        $user->socialMedia()->delete();
        // $user->socialMedia()->createMany($request->all());

        $social_medias = $request->social_media;
        $urls = $request->url;

        foreach ($social_medias as $key => $value) {
            if ($value) {
                $user->socialMedia()->create([
                    'social_media' => $value,
                    'url' => $urls[$key],
                ]);
            }
        }

        flashSuccess('Social Media Updated Successfully');
        return back();



        // $request->validate([
        //     'current_password' => ['required', new MatchOldPassword],
        //     'password' => 'required|string|min:8|confirmed',
        //     'password_confirmation' => 'required',
        // ]);
        // $password_check = Hash::check($request->current_password, auth('user')->user()->password);

        // if ($password_check) {
        //     $user = User::findOrFail(auth('user')->id());
        //     $user->update([
        //         'password' => bcrypt($request->password),
        //         'updated_at' => Carbon::now(),
        //     ]);

        //     flashSuccess('Password Updated Successfully');
        //     return back();
        // } else {
        //     flashError('Something went wrong');
        //     return back();
        // }
    }

    public function addToWishlist(Request $request)
    {
        $ad = Ad::findOrFail($request->ad_id);
        $data = Wishlist::where('ad_id', $request->ad_id)->whereUserId($request->user_id)->first();
        if ($data) {
            $data->delete();

            $user = auth('user')->user();
            if (checkSetup('mail')) {
                $user->notify(new AdWishlistNotification($user, 'remove', $ad->slug));
            }
            flashSuccess('Ad removed from wishlist');
        } else {
            Wishlist::create([
                'ad_id' => $request->ad_id,
                'user_id' => $request->user_id
            ]);

            $user = auth('user')->user();
            if (checkSetup('mail')) {
                $user->notify(new AdWishlistNotification($user, 'add', $ad->slug));
            }
            flashSuccess('Ad added to wishlist');
        }
        resetSessionWishlist();

        return back();
    }

    public function deleteAccount(User $customer)
    {
        $customer->delete();
        Auth::guard('user')->logout();
        return redirect()->route('users.login');
    }

    /**
     * Update ad status to expire
     *
     * @param Ad $ad
     *
     * @return void
     */
    public function markExpired(Ad $ad)
    {
        $ad->update([
            'status' => 'sold'
        ]);

        flashSuccess('Status updated successfully!');
        return back();
    }

    /**
     * Update ad status to expire
     *
     * @param Ad $ad
     *
     * @return void
     */
    public function markActive(Ad $ad)
    {
        $ad->update([
            'status' => 'active'
        ]);

        flashSuccess('Status updated successfully!');
        return back();
    }

    /**
     * View Post Rules Page
     *
     * @return View
     */
    public function postRules()
    {
        return view('frontend.posting-rules')->withSetting(Setting::first());
    }
}
