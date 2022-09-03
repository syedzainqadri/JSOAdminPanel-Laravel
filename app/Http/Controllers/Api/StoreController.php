<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\User;
use Modules\Brand\Entities\Brand;
use App\Models\Company;
use Illuminate\Support\Str;
use Validator;

class StoreController extends Controller
{
    public function create_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'user_id' => 'required|numeric',
            'brand_id' => 'required|numeric',
            'company_id' => 'required|numeric',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }
        $brand = Brand::find($request->brand_id);
        if (!$brand) {
            return response()->json('Brand id not found');
        }
        $company = Company::find($request->company_id);
        if (!$company) {
            return response()->json('Company id not found');
        }
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json('User id not found');
        }

        $data['name'] = $request->name;
        $data['brand_id'] = $request->brand_id;
        $data['user_id'] = $request->user_id;
        $data['company_id'] = $request->company_id;
        $data['address'] = $request->address;

        $store = Store::create($data);
        return $store;
    }

    public function update_store(Request $request, $store_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'user_id' => 'required|numeric',
            'brand_id' => 'required|numeric',
            'company_id' => 'required|numeric',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }
        $brand = Brand::find($request->brand_id);
        if (!$brand) {
            return response()->json('Brand id not found');
        }
        $company = Company::find($request->company_id);
        if (!$company) {
            return response()->json('Company id not found');
        }
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json('User id not found');
        }

        $store = Store::find($store_id);
        if (!$store) {
            return response()->json('Store not found');
        }
        $data['name'] = $request->name;
        $data['user_id'] = $request->user_id;
        $data['brand_id'] = $request->brand_id;
        $data['company_id'] = $request->company_id;
        $data['address'] = $request->address;
        $store -> update($data);
        return $store;
    }

    public function show_store(Request $request, $store_id)
    {
        $store = Store::findOrFail($store_id);
        return $store;
    }

    public function delete_store(Request $request, $store_id)
    {
        $store = Store::where('id', $store_id)->delete();
        return $store ? response()->json('Store deleted successfully') : $store;
    }
}
