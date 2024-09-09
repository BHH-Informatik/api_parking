<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function getUser(Request $request) {

        $request -> validate([
            'user_id' => 'required|integer',
        ]);

        try {
            return response()->json(['message' => 'Getting user information was successful', 'user' => User::findOrFail($request->user_id)], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Getting user information failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function getUsers() {
        return response()->json(['message' => 'Getting users information was successful', 'users' => User::all()], 200);
    }

    public function deleteUser(Request $request) {

        $request -> validate([
            'user_id' => 'required|integer',
        ]);

        try {
            User::findOrFail($request->user_id)->delete();
            return response()->json(['message' => 'User successfully deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Deletion of user failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function changeEmail(Request $request) {

        $request -> validate([
            'user_id' => 'required|integer',
            'email' => 'required|string|email'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User not found', 'error' => $e->getMessage()], 400);
        }

        $user->update([
            'email' => $request->email
        ]);

        return response()->json(['message' => 'Email successfully changed'], 200);
    }
}
