<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Notifications\ResetPassword;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email',
        ]);

        $customer = User::where('email', $request->email)->first();
        $token = rand(1000, 9999);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Email address not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if (checkSetup('mail')) {
            $customer->notify(new ResetPassword($token));
        }
        $customer->update(['token' => $token]);

        return response()->json([
            'success' => true,
            'message' => 'We have emailed your password reset code',
        ], Response::HTTP_OK);
    }
}
