<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
{
    $coach = $request->user();

    $notifications = Notification::where('coach_id', $coach->id)
        ->latest()
        ->get();

    return response()->json($notifications);
}
}
