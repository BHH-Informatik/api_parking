<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @group User Management
     * Change User Email
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @bodyParam email string required The new email address for the user. Example: john.doe@example.com
     *
     * @response 200 scenario="Success" {
     *   "message": "Email successfully changed"
     * }
     *
     * @response 422 scenario="Validation Error" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": [
     *       "The email field is required.",
     *       "The email must be a valid email address.",
     *       "The email has already been taken."
     *     ]
     *   }
     * }
     */
    public function changeEmail(Request $request) {

        $request->validate([
            'email' => 'required|string|email|unique:users|max:255',
        ]);

        Auth::user()->update([
            'email' => $request->email
        ]);

        return response()->json(['message' => 'Email successfully changed'], 200);
    }

    /**
     * @group User Management
     * Change User Name
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @bodyParam first_name string required The new first name of the user. Example: John
     * @bodyParam last_name string required The new last name of the user. Example: Doe
     *
     * @response 200 scenario="Success" {
     *   "message": "Name successfully changed"
     * }
     *
     * @response 422 scenario="Validation Error" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "first_name": [
     *       "The first name field is required.",
     *       "The first name must be a string.",
     *       "The first name may not be greater than 255 characters."
     *     ],
     *     "last_name": [
     *       "The last name field is required.",
     *       "The last name must be a string.",
     *       "The last name may not be greater than 255 characters."
     *     ]
     *   }
     * }
     */
    public function changeName(Request $request) {

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255'
        ]);

        Auth::user()->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        return response()->json(['message' => 'Name successfully changed'], 200);
    }

    /**
     * @group User Management
     * Change User Password
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @bodyParam old_password string required The current password of the user. Example: oldPassword123
     * @bodyParam new_password string required The new password for the user. Must be at least 8 characters long. Example: newPassword123
     * @bodyParam new_password_confirmation string required Confirmation of the new password. Example: newPassword123
     *
     * @response 200 scenario="Success" {
     *   "message": "Password successfully changed"
     * }
     *
     * @response 401 scenario="Incorrect Credentials" {
     *   "message": "The provided credentials are incorrect"
     * }
     *
     * @response 422 scenario="Validation Error" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "old_password": [
     *       "The old password field is required."
     *     ],
     *     "new_password": [
     *       "The new password field is required.",
     *       "The new password must be at least 8 characters."
     *     ]
     *   }
     * }
     */
    public function changePassword(Request $request) {

        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|confirmed|min:8'
        ]);

        if(!Hash::check($request->old_password, Auth::user()->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect', 401], 200);
        }

        Auth::user()->update([
            'password' =>  Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Password successfully changed'], 200);
    }
}
