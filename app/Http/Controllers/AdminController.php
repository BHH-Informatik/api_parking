<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// @group Admin
// API endpoints for admin users
// @group User Management
// API endpoints to manage users
class AdminController extends Controller {


    /**
     * @group User Management
     *
     * Create User
     * @authenticated
     * @bodyParam first_name string required User-First-Name. Example: John
     * @bodyParam last_name string required User-Last-Name. Example: Doe
     * @bodyParam email string required User-Email. Example: john@example.com
     * @bodyParam password string required User-Password. Example: 12345678
     * @bodyParam password_confirmation string required User-Password-Confirmation. Example: 12345678
     * @bodyParam role string optional User-Role. Example: admin
     * @response 201 scenario="Success" {"message":"User successfully created","user":{"first_name":"John","last_name":"Doe","email":"
     * @response 400 scenario="Bad Request" {"message":"User creation failed","error":"..."}
     *
     * */
    public function createUser(Request $request) {

        $params = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|confirmed|min:8',
            'role' => 'nullable|string|exists:roles,name'
        ]);

        $params['password'] = Hash::make($params['password']);
        $user = User::create($params);

        if(isset($params['role'])){
            $user->assignRole($params['role']);
        }else{
            $user->assignRole('user');
        }

        return response()->json(['message' => 'User successfully created', 'user' => $user], 201);

    }


    /**
     * @group User Management
     *
     * Get User
     * @authenticated
     * @urlParam id required User-ID. Example: 1
     * @response 200 scenario="Success" {"message":"Getting user information was successful","user":{"first_name":"John","last_name":"Doe","email":"
     * @response 400 scenario="Bad Request" {"message":"Getting user information failed","error":"..."}
     *
     * */
    public function getUser(Request $request, $id) {
        try {

            return response()->json(['message' => 'Getting user information was successful', 'user' => User::findOrFail($id)], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Getting user information failed', 'error' => $e->getMessage()], 400);
        }
    }


    /**
     * @group User Management
     *
     * Get Users
     * @authenticated
     * @queryParam limit integer optional Limit. Example: 50
     * @queryParam offset integer optional Offset. Example: 0
     * @response 200 scenario="Success" {"message":"Getting users information was successful","users":[{"first_name":"John","last_name":"Doe","email":"
     * @response 400 scenario="Bad Request" {"message":"Getting users information failed","error":"..."}
     *
     * */
    public function getUsers(Request $request) {

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $users = User::limit($limit)->offset($offset)->get();

        return response()->json([
            'message' => 'Getting users information was successful',
            'users' => $users
        ], 200);
    }


    /**
     * @group User Management
     *
     * Delete User
     * @authenticated
     * @urlParam id required User-ID. Example: 1
     * @response 200 scenario="Success" {"message":"User successfully deleted"}
     * @response 400 scenario="Bad Request" {"message":"Deletion of user failed","error":"..."}
     *
     * */
    public function deleteUser(Request $request, $id) {
        try {
            User::findOrFail($id)->delete();
            return response()->json(['message' => 'User successfully deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Deletion of user failed', 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * @group User Management
     * Update User
     *
     * @authenticated
     * @urlParam id required User-ID. Example: 1
     * @bodyParam first_name string optional User-First-Name. Example: John
     * @bodyParam last_name string optional User-Last-Name. Example: Doe
     * @bodyParam email string optional User-Email. Example: john@example.com
     * @response 200 scenario="Success" {"id":1,"first_name":"John","last_name":"Doe","email":"john@example.com", ...}
     * @response 400 scenario="Bad Request" {"message":"User update failed","error":"..."}
     *
     */
    public function updateUser(Request $request, $id) {

        $user = User::findOrFail($id);

        $params = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->fill($params);
        if ($user->isDirty()) {
            $user->save();
        }

        return response()->json($user);
    }
}
