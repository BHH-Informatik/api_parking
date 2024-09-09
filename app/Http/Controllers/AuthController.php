<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function register(Request $request){

        $params = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|confirmed|min:8',
        ]);
        $password = $params["password"];

        $params['password'] = Hash::make($params['password']);

        try {
            $user = User::create($params);
            $user->assignRole('user');

            $token = Auth::attempt([
                "email" => $params["email"],
                "password" => $password,
            ]);
            return $this->respondWithToken($token, $user);
            // return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User registration failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request){

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ( ! $token = auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'The provided credentials are incorrect'], 401);
        }

        return $this->respondWithToken($token);

        // return response()->json([
        //     'message' => 'Login successful',
        //     "token" => $token,
        //     'user' => auth()->user(),
        //     'token_type' => 'bearer',
        //     'expires_in' => auth()->factory()->getTTL() * 60
        // ], 200);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Logout successful'], 200);
    }

    protected function respondWithToken($token, $user = null){

        if($user == null){
            $user = auth()->user();
        }

        // just return the role names
        // $user->roles = $user->getRoleNames()->pluck('name')->toArray();

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
