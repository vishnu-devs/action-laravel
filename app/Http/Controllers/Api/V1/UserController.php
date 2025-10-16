<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
        ]);

        $user->fill($request->only(['name', 'company_name', 'company_address']));
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $user = $request->user();
        $user->avatar = $path; // assumes users table has avatar column; placeholder
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_url' => asset('storage/' . $path)
            ]
        ], 201);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
        ]);
    }
}