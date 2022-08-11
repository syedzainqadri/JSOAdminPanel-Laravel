<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public $authApiGuard;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
        $this->authApiGuard = auth('api');
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string',
            'username' => 'sometimes|string',
        ]);

        try {
            $login_type = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $credentials = [$login_type => $request->username, 'password' => $request->password];

            if (!$token = $this->authApiGuard->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Crendentials',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $this->createNewToken($token);
        } catch (JWTException $e) {
            return response()->json("failed", "An error occured, please contact support.", 500);
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|between:2,100',
            'email' => "required|string|max:100|email|unique:users,email",
            'password' => "required|min:8|max:50",
        ]);

        $usernameExists = User::where('username', Str::slug($request->name))->count();

        if ($usernameExists) {
            $username = Str::slug($request->name) . '_' . Str::random(5);
        } else {
            $username = Str::slug($request->name);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'username' => $username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => 'User registered',
            'user' => $user
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->authApiGuard->logout();

        return response()->json(['message' => 'User logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken($this->authApiGuard->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authUser()
    {
        return response()->json($this->authApiGuard->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'token' => $token,
            'user' => $this->authApiGuard->user(),
            // 'token_type' => 'bearer'
            'expires_in' => $this->authApiGuard->factory()->getTTL() * 60 * 24 * 30
        ]);
    }
}
