<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminController extends Controller {

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

    public function getUser(Request $request, $id) {
        try {

            return response()->json(['message' => 'Getting user information was successful', 'user' => User::findOrFail($id)], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Getting user information failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function getUsers() {




        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $users = User::limit($limit)->offset($offset)->get();

        return response()->json([
            'message' => 'Getting users information was successful',
            'users' => $users
        ], 200);
    }

    public function deleteUser(Request $request, $id) {
        try {
            User::findOrFail($id)->delete();
            return response()->json(['message' => 'User successfully deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Deletion of user failed', 'error' => $e->getMessage()], 400);
        }
    }

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
