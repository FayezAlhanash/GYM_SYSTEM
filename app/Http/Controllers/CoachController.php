<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CoachController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|min:10|max:15|unique:coaches,phone',
            'gender' => ['required', Rule::in(['male', 'female'])],
            'password' => 'required|string|min:8|confirmed',
            'location' => 'required|string',
            'gym_name' => 'required|string',
            'birth_date'  => 'required|date_format:Y-m-d',
        ]);
        $coach = Coach::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'birth_date' => $request->birth_date,
            'gym_name' => $request->gym_name,
            'location' => $request->location,
            'gender' => $request->gender,
            'status'     => 'pending',
        ]);
        $token = $coach->createToken('auth_Token')->plainTextToken;
        return response()->json([
            'message' => 'User registered succesfully',
            'COACH' => $coach,
            'Token' => $token
        ], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $coach = Coach::where('phone', $request->phone)->first();

        if (!$coach || !Hash::check($request->password, $coach->password)) {
            return response()->json([
                'message' => 'Invalid phone or password'
            ], 401);
        }
        if ($coach->status !== 'active') {

        $message = match ($coach->status) {
            'blocked' => 'Your account has been blocked',
            'pending' => 'Your account is not approved yet',
            default   => 'Account not allowed',
        };

        return response()->json([
            'message' => $message
        ], 403);
    }
    
        $token = $coach->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'coach'   => $coach,
            'token'   => $token
        ]);
    }

    public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
}
}
