<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => "required|string|max:100|email",
            'password' => "required|min:8|max:50",
        ]);

        $customer = User::where('email', $request->email)->first();

        if ($customer->token == $request->token) {
            $customer->update([
                'token' => null,
                'password' => bcrypt($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid token',
        ], Response::HTTP_NOT_FOUND);
    }
}
