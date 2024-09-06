<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function assignAdminRole(Request $request) {

        $request -> validate([
            'user_id' => 'required|integer',
        ]);

        $id = $request -> user_id;

        try {
            $user = User::findOrFail($id);
        } catch (\Exception $e){
            return response()->json(['message' => 'User not found', 'error' => $e->getMessage()], 400);
        }

        $user->assignRole('admin');
        $user->removeRole('user');

        return response()->json(['message' => 'Role successfully assigned'], 200);
    }

    public function removeAdminRole(Request $request) {
        $request -> validate([
            'user_id' => 'required|integer',
        ]);

        $id = $request -> user_id;

        try {
            $user = User::findOrFail($id);
        } catch (\Exception $e){
            return response()->json(['message' => 'User not found', 'error' => $e->getMessage()], 400);
        }

        $user->assignRole('user');
        $user->removeRole('admin');

        return response()->json(['message' => 'Role successfully removed'], 200);
    }
}
