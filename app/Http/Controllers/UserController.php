<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function changeEmail(Request $request) {

        $request->validate([
            'email' => 'required|string|email|unique:users|max:255',
        ]);

        Auth::user()->update([
            'email' => $request->email
        ]);

        return response()->json(['message' => 'Email successfully changed'], 200);
    }

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

    public function deleteUser(Request $request) {

        $request->validate([
            'password' => 'required|string',
        ]);

        if(!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect', 401], 200);
        }

        $user = Auth::user();
        Auth::logout();
        $user->delete();

        return response()->json(['message' => 'User successfully deleted'], 200);
    }

    // TODO
    public function resetPassword(Request $request) {

        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        try {
            $user = User::where('email', $request->email);
        } catch (\Exception $e){
            return response()->json(['message' => 'User not found', 'error' => $e->getMessage()], 400);
        }

        // send an email

        return response()->json(['message' => 'Password reset process was started'],200);
    }
}
