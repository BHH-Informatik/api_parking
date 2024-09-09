<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

// @group Authentication
// API endpoints for authentication
class AuthController extends Controller
{

    /**
     * @group Authentication
     *
     * Register
     * @unauthenticated
     * @bodyParam first_name string required User-First-Name. Example: John
     * @bodyParam last_name string required User-Last-Name. Example: Doe
     * @bodyParam email string required User-Email. Example: john@example.com
     * @bodyParam password string required User-Password. Example: 12345678
     * @bodyParam password_confirmation string required User-Password-Confirmation. Example: 12345678
     *
     * Register a new user
     *
     * @response 200 scenario="Success" {"access_token":"9hC4K....gMzp8nQCrgw","token_type":"bearer","expires_in":604800,"user": { "first_name": "John", "last_name": "Doe", "email": "john@example.com", "updated_at": "2021-09-29T14:00:00.000000Z", "created_at": "2021-09-29T14:00:00.000000Z", "id": 1 }}
     * @response 401 scenario="Unauthorized" {"success": false, "message": "Unauthorized"}
     * */
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

      /**
     * @group Authentication
     * Login
     *
     * @unauthenticated
     * @bodyParam email string required User-Email. Example: foo@bar.de
     * @bodyParam password string required User-Password. Example: 123456
     *
     * @response 200 scenario="Success" {"access_token":"9hC4K....gMzp8nQCrgw","token_type":"bearer","expires_in":604800,"user": { "first_name": "John", "last_name": "Doe", "email": "john@example.com", "updated_at": "2021-09-29T14:00:00.000000Z", "created_at": "2021-09-29T14:00:00.000000Z", "id": 1 }}
     * @response 401 scenario="Unauthorized" {"success": false, "message": "Unauthorized"}
     *
     */
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

    /**
     * @group Authentication
     * Logout
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     * @response 200 scenario="Sucess" {'message' => 'Logout successful'}
     */
    public function logout() {
        Auth::logout();

        return response()->json(['message' => 'Logout successful'], 200);
    }


    /**
     * @group Authentication
     * Get Self
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @response 200 scenario="Success" {"id": 1, "first_name": "John", "last_name": "Doe", "email": "john@example.com", "email_verified_at": null, "created_at": "2021-09-29T14:00:00.000000Z", "updated_at": "2021-09-29T14:00:00.000000Z"}
     */
    public function me() {
        return response()->json(auth()->user());
    }


    /**
     * @group Authentication
     * Delete Self
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     * @response 200 scenario="Sucess" {'message' => 'User successfully deleted'}
     */
    public function deleteMe() {
        $user = auth()->user();
        Auth::logout();
        $user->delete();

        return response()->json(['message' => 'User successfully deleted'], 200);
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
