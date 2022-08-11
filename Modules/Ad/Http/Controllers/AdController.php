<?php

namespace Modules\Ad\Http\Controllers;

use App\Models\User;
use App\Models\UserPlan;
use Illuminate\Support\Str;
use Modules\Ad\Entities\Ad;
use Modules\Brand\Entities\Brand;
use App\Http\Traits\AdCreateTrait;
use App\Models\Setting;
use Illuminate\Routing\Controller;
use Modules\Category\Entities\Category;
use Modules\Category\Entities\SubCategory;
use App\Notifications\AdCreateNotification;
use Modules\Ad\Http\Requests\AdFormRequest;
use App\Notifications\AdApprovedNotification;
use Illuminate\Http\Request;
use Modules\CustomField\Entities\CustomField;
use Modules\CustomField\Entities\ProductCustomField;
use Intervention\Image\Facades\Image;
use function PHPUnit\Framework\returnSelf;

class AdController extends Controller
{
    use AdCreateTrait;

    /**
     * Display a listing of the ads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!userCan('ad.view')) {
            return abort(403);
        }

        $categories = Category::active()->get(['id', 'name', 'slug']);
        $brands = Brand::get(['id', 'name', 'slug']);
        $query = Ad::query();

        // keyword search
        if (request()->has('keyword') && request()->keyword != null) {
            $query->where('title', "LIKE", "%" . request('keyword') . "%");
        }

        // category filter
        if ($request->has('category') && $request->category != null) {
            $category = $request->category;

            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        // brand filter
        if ($request->has('brand') && $request->brand != null) {
            $brand = $request->brand;

            $query->whereHas('brand', function ($q) use ($brand) {
                $q->where('slug', $brand);
            });
        }

        // filtering
        if (request()->has('filter_by') && request()->filter_by != null) {
            switch (request()->filter_by) {
                case 'sold':
                    $query->where('status', 'sold');
                    break;
                case 'active':
                    $query->where('status', 'active');
                    break;
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'declined':
                    $query->where('status', 'declined');
                    break;
                case 'featured':
                    $query->where('featured', 1)->latest();
                    break;
                case 'most_viewed':
                    $query->latest('total_views');
                    break;
                case 'all':
                    $query;
                    break;
            }
        }

        $ads = $query->latest()->paginate(10)->withQueryString();

        return view('ad::index', compact(
            'ads',
            'categories',
            'brands'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!userCan('ad.create')) {
            return abort(403);
        }

        $brands = Brand::all();
        $customers = User::all();
        $categories = Category::active()->with('subcategories', function ($q) {
            $q->where('status', 1);
        })->get();

        if ($categories->count() < 1) {
            flashWarning("You don't have any active category. Please create or active category first.");
            return redirect()->route('module.category.create');
        }

        if ($customers->count() < 1) {
            flashWarning("You don't have any customer. Please create customer first.");
            return redirect()->route('module.customer.create');
        }

        if ($brands->count() < 1) {
            flashWarning("You don't have any brand. Please create brand first.");
            return redirect()->route('module.brand.create');
        }

        return view('ad::create', [

            'brands' => $brands,
            'customers' => $customers
        ]);
    }

    public function getSubcategory($id)
    {
        echo json_encode(SubCategory::where('category_id', $id)->get());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param AdFormRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdFormRequest $request)
    {
        if (!userCan('ad.create')) {
            return abort(403);
        }

        $location = session()->get('location');
        if (!$location) {

            $request->validate([
                'location' => 'required',
            ]);
        }

        $ad = new Ad();
        $ad->title = $request->title;
        $ad->slug = Str::slug($request->title);
        $ad->user_id = $request->user_id;
        $ad->category_id = $request->category_id;
        $ad->subcategory_id = $request->subcategory_id;
        $ad->brand_id = $request->brand_id;
        $ad->price = $request->price;
        $ad->description = $request->description;
        $ad->show_phone = $request->show_phone;
        $ad->phone = $request->phone;
        $ad->phone_2 = $request->phone_2;
        $ad->whatsapp = $request->whatsapp ?? '';
        $ad->featured = $request->featured ? $request->featured : 0;
        $ad->status = setting('ads_admin_approval') ? 'pending' : 'active';
        $ad->save();

        // image uploading
        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {

            $url = uploadImage($request->thumbnail, 'addss_images', true);
            $ad->update(['thumbnail' => $url]);
        }

        // feature inserting
        foreach ($request->features as $feature) {
            if ($feature) {
                $ad->adFeatures()->create(['name' => $feature]);
            }
        }

        !setting('ads_admin_approval') ? $this->userPlanInfoUpdate($ad->featured, $request->user_id) : '';

        // <!--  location  -->
        $location = session()->get('location');

        $region = array_key_exists("region", $location) ? $location['region'] : '';
        $country = array_key_exists("country", $location) ? $location['country'] : '';
        $address = Str::slug($region . '-' . $country);

        $ad->update([
            'address' => $address,
            'neighborhood' => array_key_exists("neighborhood", $location) ? $location['neighborhood'] : '',
            'locality' => array_key_exists("locality", $location) ? $location['locality'] : '',
            'place' => array_key_exists("place", $location) ? $location['place'] : '',
            'district' => array_key_exists("district", $location) ? $location['district'] : '',
            'postcode' => array_key_exists("postcode", $location) ? $location['postcode'] : '',
            'region' => array_key_exists("region", $location) ? $location['region'] : '',
            'country' => array_key_exists("country", $location) ? $location['country'] : '',
            'long' => array_key_exists("lng", $location) ? $location['lng'] : '',
            'lat' => array_key_exists("lat", $location) ? $location['lat'] : '',
        ]);

        session()->forget('location');

        flashSuccess('Ad Created Successfully. Please add category custom field values .');

        return redirect()->route('module.ad.custom.field.value', $ad->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Ad $ad
     * @return \Illuminate\Http\Response
     */
    public function show(Ad $ad)
    {
        if (!userCan('ad.view')) {
            return abort(403);
        }

        $ad->load('adFeatures', 'galleries', 'category', 'subcategory', 'customer', 'galleries', 'brand', 'productCustomFields');

        return view('ad::show', compact('ad'));
    }


    public function edit(Ad $ad)
    {
        $data['brands'] = Brand::get();
        $data['customers'] = User::get();

        $data['categories'] = Category::get();
        $data['subcategories'] = SubCategory::where('category_id', $ad->category->id)->get();

        return view('ad::edit', compact('ad'), $data);
    }

    public function update(AdFormRequest $request, Ad $ad)
    {
        if (!userCan('ad.update')) {
            return abort(403);
        }

        $ad->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'user_id' => $request->user_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'brand_id' => $request->brand_id,
            'price' => $request->price,
            'description' => $request->description,
            'phone' => $request->phone,
            'show_phone' => $request->show_phone,
            'phone_2' => $request->phone_2,
            'whatsapp' => $request->whatsapp ?? '',
            'featured' => $request->featured ? $request->featured : 0,
        ]);

        // image updating
        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            deleteImage($ad->thumbnail);

            $url = uploadImage($request->thumbnail, 'addss_image', true);
            $ad->update(['thumbnail' => $url]);
        }

        // feature inserting
        $ad->adFeatures()->delete();
        foreach ($request->features as $feature) {
            if ($feature) {
                $ad->adFeatures()->create(['name' => $feature]);
            }
        }

        // <!--  location  -->
        $location = session()->get('location');
        if ($location) {

            $region = array_key_exists("region", $location) ? $location['region'] : '';
            $country = array_key_exists("country", $location) ? $location['country'] : '';
            $address = Str::slug($region . '-' . $country);

            $ad->update([
                'address' => $address,
                'neighborhood' => array_key_exists("neighborhood", $location) ? $location['neighborhood'] : '',
                'locality' => array_key_exists("locality", $location) ? $location['locality'] : '',
                'place' => array_key_exists("place", $location) ? $location['place'] : '',
                'district' => array_key_exists("district", $location) ? $location['district'] : '',
                'postcode' => array_key_exists("postcode", $location) ? $location['postcode'] : '',
                'region' => array_key_exists("region", $location) ? $location['region'] : '',
                'country' => array_key_exists("country", $location) ? $location['country'] : '',
                'long' => array_key_exists("lng", $location) ? $location['lng'] : '',
                'lat' => array_key_exists("lat", $location) ? $location['lat'] : '',
            ]);

            session()->forget('location');
        }


        flashSuccess('Ad Updated Successfully. Please update category custom field values .');
        return redirect()->route('module.ad.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Ad $ad
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ad $ad)
    {
        if (!userCan('ad.delete')) {
            return abort(403);
        }

        try {

            if (file_exists(public_path($ad->thumbnail))) {
                @unlink(public_path($ad->thumbnail));
            }

            $ad->delete();

            flashSuccess('Ad Deleted Successfully');
            return back();
        } catch (\Throwable $th) {
            flashError();
            return back();
        }
    }

    public function status(Ad $ad, $status)
    {
        $customer = $ad->customer;
        $ad_status = $ad->status;

        if ($ad) {
            $ad->update(['status' => $status]);
            flashSuccess('Ad Status Updated Successfully');

            if ($ad_status == 'pending') {
                if ($status == 'active') {
                    $this->userPlanInfoUpdate($ad->featured, $ad->user_id);
                    if (checkSetup('mail')) {
                        $customer->notify(new AdApprovedNotification($customer, $ad));
                        $customer->notify(new AdCreateNotification($customer, $ad));
                    }
                }
            }

            return back();
        }

        flashError();
        return back();
    }

    public function addCustomFieldValue(Ad $ad)
    {
        $category = Category::with('customFields.values')->FindOrFail($ad->category_id);

        return view('customfield::ad_create_field', compact('ad', 'category'));
    }

    public function editCustomFieldValue(Ad $ad)
    {
        $category_custom_fields = $category = Category::FindOrFail($ad->category_id)->customFields->pluck('id')->toArray();
        $ad_custom_fields = ProductCustomField::where('ad_id', $ad->id)->pluck('custom_field_id')->toArray();

        foreach ($category_custom_fields as $key => $categoey_custom_field_id) {
            if (!in_array($categoey_custom_field_id, $ad_custom_fields)) {
                $new_field = $categoey_custom_field_id;
            }
        }

        if (isset($new_field) && $new_field) {
            $CustomField = CustomField::with('values')->findOrFail($new_field);
            $values = $CustomField->values;

            if (isset($values) && count($values)) {
                $ad->productCustomFields()->create([
                    'custom_field_id' => $CustomField->id,
                    'value' => $values[0]->value,
                    'custom_field_group_id' => $CustomField->custom_field_group_id,
                ]);
            } else {
                $ad->productCustomFields()->create([
                    'custom_field_id' => $CustomField->id,
                    'value' => '',
                    'custom_field_group_id' => $CustomField->custom_field_group_id,
                ]);
            }
        }


        $ad->load('productCustomFields');

        return view('customfield::ad_edit_field', compact('ad'));
    }

    public function storeCustomFieldValue(Request $request, Ad $ad)
    {
        $category = Category::with('customFields.values')->FindOrFail($ad->category_id);

        foreach ($category->customFields as $field) {
            if ($field->slug !== $request->has($field->slug) && $field->required) {
                if ($field->type != 'checkbox' && $field->type != 'checkbox_multiple') {
                    $request->validate([
                        $field->slug => 'required',
                    ]);
                }
            }
            if ($field->type == 'textarea') {
                $request->validate([
                    $field->slug => 'max:255',
                ]);
            }
            if ($field->type == 'url') {
                $request->validate([
                    $field->slug => 'url',
                ]);
            }
            if ($field->type == 'number') {
                $request->validate([
                    $field->slug => 'numeric',
                ]);
            }
            if ($field->type == 'date') {
                $request->validate([
                    $field->slug => 'date',
                ]);
            }
        }

        // First Delete If Custom Field Value exist for this Ad
        $field_values = ProductCustomField::where('ad_id', $ad->id)->get();
        foreach ($field_values as $item) {

            if (file_exists($item->value)) {
                unlink($item->value);
            }
            $item->delete();
        }

        $checkboxFields = [];
        if (request()->filled('cf')) {
            $checkboxFields = request()->get('cf');
        }

        // Checkbox Fields
        if ($checkboxFields) {
            foreach ($checkboxFields as $key => $values) {
                $CustomField = CustomField::findOrFail($key)->load('customFieldGroup');

                if (gettype($values) == 'array') {
                    $imploded_value = implode(", ", $values);

                    $ad->productCustomFields()->create([
                        'custom_field_id' => $key,
                        'value' => $imploded_value,
                        'custom_field_group_id' => $CustomField->custom_field_group_id,
                    ]);
                } else {
                    $ad->productCustomFields()->create([
                        'custom_field_id' => $key,
                        'value' => $values ?? '0',
                        'custom_field_group_id' => $CustomField->custom_field_group_id,
                    ]);
                }
            }
        }

        // then insert
        foreach ($category->customFields as $field) {
            if ($field->slug == $request->has($field->slug)) {
                $CustomField = CustomField::findOrFail($field->id)->load('customFieldGroup');

                // check data type for confirm it is image
                $fileType = gettype(request($field->slug));

                if ($fileType == 'object') {
                    $image = uploadImage(request($field->slug), '/custom-field/');
                }

                $ad->productCustomFields()->create([
                    'custom_field_id' => $field->id,
                    'value' => $fileType == 'object' ? $image : request($field->slug),
                    'custom_field_group_id' => $CustomField->custom_field_group_id,
                ]);
            }
        }

        flashSuccess('Ad Created Successfully. Please add the ad gallery images.');
        return redirect()->route('module.ad.show_gallery', $ad->id);
    }

    public function updateCustomFieldValue(Request $request, Ad $ad)
    {
        $category = Category::with('customFields.values')->FindOrFail($ad->category_id);

        foreach ($category->customFields as $field) {
            if ($field->slug !== $request->has($field->slug) && $field->required) {
                if ($field->type != 'checkbox' && $field->type != 'checkbox_multiple') {
                    $request->validate([
                        $field->slug => 'required',
                    ]);
                }
            }
            if ($field->type == 'textarea') {
                $request->validate([
                    $field->slug => 'max:255',
                ]);
            }
            if ($field->type == 'url') {
                $request->validate([
                    $field->slug => 'url',
                ]);
            }
            if ($field->type == 'number') {
                $request->validate([
                    $field->slug => 'numeric',
                ]);
            }
            if ($field->type == 'date') {
                $request->validate([
                    $field->slug => 'date',
                ]);
            }
        }

        // First Delete If Custom Field Value exist for this Ad
        $field_values = ProductCustomField::with('customField')->where('ad_id', $ad->id)->get();
        foreach ($field_values as $item) {
            $item->delete();
        }

        // then insert
        foreach ($category->customFields as $field) {
            if ($field->slug == $request->has($field->slug)) {
                $CustomField = CustomField::findOrFail($field->id)->load('customFieldGroup');

                // check data type for confirm it is image
                $fileType = gettype(request($field->slug));

                if ($fileType == 'object') {
                    $image = uploadImage(request($field->slug), '/custom-field/');
                }

                $ad->productCustomFields()->create([
                    'custom_field_id' => $field->id,
                    'value' => $fileType == 'object' ? $image : request($field->slug),
                    'custom_field_group_id' => $CustomField->custom_field_group_id,
                ]);
            }
        }

        $checkboxFields = [];
        if (request()->filled('cf')) {
            $checkboxFields = request()->get('cf');
        }

        // Checkbox Fields
        if ($checkboxFields) {
            foreach ($checkboxFields as $key => $values) {
                $CustomField = CustomField::findOrFail($key)->load('customFieldGroup');

                if (gettype($values) == 'array') {
                    $imploded_value = implode(", ", $values);

                    $ad->productCustomFields()->create([
                        'custom_field_id' => $key,
                        'value' => $imploded_value,
                        'custom_field_group_id' => $CustomField->custom_field_group_id,
                    ]);
                } else {
                    $ad->productCustomFields()->create([
                        'custom_field_id' => $key,
                        'value' => $values ?? '0',
                        'custom_field_group_id' => $CustomField->custom_field_group_id,
                    ]);
                }
            }
        }

        flashSuccess('Ad Updated Successfully');
        return redirect()->route('module.ad.index');
    }

    public function sortingCustomFieldValue(Ad $ad)
    {
        $ad =  $ad->load(['productCustomFields' => function ($q) {
            $q->without('customField.values', 'customField.customFieldGroup');
        }]);

        $fields = $ad->productCustomFields;

        return view('customfield::ad_sorting_field', compact('ad', 'fields'));
    }

    public function sortingCustomFieldValueStore(Request $request)
    {
        try {
            $fields = ProductCustomField::where('ad_id', $request->ad)->get();

            foreach ($fields as $task) {
                $task->timestamps = false;
                $id = $task->id;

                foreach ($request->order as $order) {
                    if ($order['id'] == $id) {
                        $task->update(['order' => $order['position']]);
                    }
                }
            }

            return response()->json(['message' => 'Custom Field Sorted Successfully!']);
        } catch (\Throwable $th) {
            flashError();
            return back();
        }
    }
}
