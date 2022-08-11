<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;

class SettingController extends Controller
{
    public function appSetting()
    {
        $payment_setting = PaymentSetting::first();
        $setting = Setting::first();
        $setting['app_name'] = env('APP_NAME');
        $setting['app_url'] = env('APP_URL');
        $setting['payment_setting'] = $payment_setting;

        return response()->json([
            'success' => true,
            'data' => $setting,
        ], Response::HTTP_OK);
    }
}
