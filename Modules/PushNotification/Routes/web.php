<?php

use Illuminate\Support\Facades\Route;
use Modules\PushNotification\Http\Controllers\PushNotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/push-notification', [PushNotificationController::class, 'index']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('/store-token', [PushNotificationController::class, 'updateDeviceToken'])->name('store.token');
    Route::post('/send-web-notification', [PushNotificationController::class, 'sendNotification'])->name('send.web-notification');
});

// push notification configuration update routes for admin
Route::middleware(['auth:admin'])->group(function () {
    Route::put('/admin/push/notification/update', [PushNotificationController::class, 'update'])->name('admin.push.notification.update');
    Route::post('/admin/push/notification/status/update', [PushNotificationController::class, 'statusUpdate'])->name('admin.push.notification.status.update');
});
