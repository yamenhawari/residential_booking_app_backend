<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  public function register(Request $request)
  {
    $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'phone' => 'required|string|unique:users,phone',
      'password' => 'required|string|min:8|confirmed',
      'profile_image' => 'required|image|mimes:png,jpg,jpeg,gif|max:2048',
      'birth_date' => 'required',
      'id_image' => 'required|image|mimes:png,jpg,jpeg,gif|max:2048',
      'role' => 'required|in:owner,tenant',
      'fcm_token' => 'nullable|string'
    ]);

    $path = null;
    if ($request->hasFile('profile_image')) {
      $path = $request->file('profile_image')->store('personal', 'public');
    }

    $idpath = null;
    if ($request->hasFile('id_image')) {
      $idpath = $request->file('id_image')->store('ids', 'public');
    }

    $user = User::create([
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'phone' => $request->phone,
      'password' => Hash::make($request->password),
      'profile_image' => $path,
      'birth_date' => $request->birth_date,
      'id_image' => $idpath,
      'role' => $request->role,
      'status' => 'inactive',
      'fcm_token' => $request->fcm_token
    ]);

    return response()->json([
      'message' => 'User Registered Successfully',
      'User' => $user
    ], 201);
  }

  public function login(Request $request)
  {
    $request->validate([
      'phone' => 'required|string',
      'password' => 'required|string',
      'fcm_token' => 'nullable|string',
    ]);

    if (!Auth::attempt($request->only('phone', 'password'))) {
      return response()->json(['message' => 'Invalid phone or password'], 401);
    }

    $user = User::where('phone', $request->phone)->firstOrFail();

    if ($user->status === 'inactive') {
      return response()->json([
        'message' => 'Your account is pending admin approval.',
        'status' => 'inactive',
        'User' => $user
      ], 403);
    }

    if ($user->status === 'blocked') {
      return response()->json(['message' => 'Your account has been blocked.'], 403);
    }

    if ($request->filled('fcm_token')) {
      $user->update(['fcm_token' => $request->fcm_token]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'message' => 'Login Successfully',
      'User' => $user,
      'Token' => $token
    ], 200);
  }

  public function logout(Request $request)
  {
    $user = $request->user();

    if ($user) {
      $user->update(['fcm_token' => null]);
      $user->currentAccessToken()->delete();
    }

    return response()->json(['message' => 'Logged out successfully']);
  }

  public function updateFcmToken(Request $request)
  {
    $request->validate(['fcm_token' => 'required|string']);

    $request->user()->update([
      'fcm_token' => $request->fcm_token
    ]);

    return response()->json(['message' => 'Token updated successfully']);
  }
}
