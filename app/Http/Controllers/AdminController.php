<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $admin = Admin::where('email', $request->email)->first();

    if (!$admin || !Hash::check($request->password, $admin->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $admin->createToken('admin_token')->plainTextToken;

    return response()->json([
        'admin' => $admin,
        'token' => $token
    ]);
}
public function pendingCoaches()
{
    $coaches = Coach::where('status', 'pending')->get();

    return response()->json($coaches);
}
public function approveCoach($id)
{
    $coach = Coach::findOrFail($id);

    $coach->status = 'active';
    $coach->save();

    return response()->json([
        'message' => 'Coach activated successfully'
    ]);
}

public function rejectCoach($id)
{
    $coach = Coach::findOrFail($id);

    $coach->status = 'blocked';
    $coach->save();

    return response()->json([
        'message' => 'Coach rejected successfully'
    ]);
}
}
