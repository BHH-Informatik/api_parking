<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){

        $params = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $params['password'] = Hash::make($params['password']);

        try {
            $user = User::create($params);
            $user->assignRole('user');
            Auth::login($user);
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User registration failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request){

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Login successful', 'user' => Auth::user()], 200);
        }

        return response()->json(['message' => 'The provided credentials are incorrect'], 401);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Logout successful'], 200);
    }
}
