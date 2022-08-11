<?php

namespace Modules\PushNotification\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Renderable;
use Modules\PushNotification\Entities\UserDeviceToken;

class PushNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('pushnotification::index');
    }

    public function updateDeviceToken(Request $request)
    {
        $old_token = UserDeviceToken::where('device_token', $request->token)->first();

        if (!$old_token) {
            UserDeviceToken::create([
                'user_id' => auth()->id(),
                'device_token' => $request->token
            ]);
        }

        return response()->json(['Token successfully stored.']);
    }

    public function sendNotification($userId, $name, $message)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $tokens = UserDeviceToken::where('user_id', $userId)->get();
        $FcmToken = [];
        foreach ($tokens as $token) {
            array_push($FcmToken, $token->device_token);
        }

        $serverKey = setting('server_key'); // ADD SERVER KEY HERE PROVIDED BY FCM

        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => 'New message from ' . $name,
                "body" => $message,
            ]
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        // FCM response
        return response()->json($result);
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('pushnotification::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('pushnotification::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('pushnotification::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $request->validate([
            'server_key' => 'required',
            'api_key' => 'required',
            'auth_domain' => 'required',
            'project_id' => 'required',
            'storage_bucket' => 'required',
            'messaging_sender_id' => 'required',
            'app_id' => 'required',
            'measurement_id' => 'required'
        ]);

        $setting = Setting::first();
        $setting->update([
            'push_notification_status' => $request->push_notification_status ? 1 : 0,
            'server_key' => $request->server_key,
            'api_key' => $request->api_key,
            'auth_domain' => $request->auth_domain,
            'project_id' => $request->project_id,
            'storage_bucket' => $request->storage_bucket,
            'messaging_sender_id' => $request->messaging_sender_id,
            'app_id' => $request->app_id,
            'measurement_id' => $request->measurement_id,
        ]);

        flashSuccess('Push notification configuration updated .');
        return redirect()->back();
    }

    public function statusUpdate(Request $request)
    {
        $setting = Setting::first();
        $setting->update([
            'push_notification_status' => $request->status ? 1 : 0,
        ]);
        return ['success' => true];
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
