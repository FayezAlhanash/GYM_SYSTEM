<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function createSub(Request $request, $id)
    {
        $player = Player::where('id', $id)->where('coach_id', $request->user()->id)->firstOrFail();
        $today = Carbon::today();
        $request->validate([
            'days' => 'required|in:15,30,60,90,365'
        ]);
        $activeSubscription = $player->subscriptions()
            ->whereDate('end_date', '>=', $today)
            ->latest('end_date')
            ->first();

        if ($activeSubscription) {
            return response()->json([
                'message' => 'Player already has an active subscription. Use renew instead.'
            ], 400);
        }
        $player = Player::where('id', $id)->where('coach_id', $request->user()->id)->firstOrFail();
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays((int) $request->days);
        $subscription = Subscription::create([
            'player_id' => $id,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return response()->json([
            'message' => 'Subscription is Added',
            'subscription' => $subscription
        ], 201);
    }

    public function renew(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|in:15,30,60,90,365',
            'renew_from' => 'required|in:today,lastSub'
        ]);

        $player = Player::where('id', $id)
            ->where('coach_id', $request->user()->id)
            ->firstOrFail();

        $lastSubscription = $player->subscriptions()
            ->latest('end_date')
            ->first();

        if (!$lastSubscription) {
            return response()->json([
                'message' => 'No previous subscription found'
            ], 400);
        }

        $today = Carbon::today();

        // ✅ إذا الاشتراك لسه شغال → نعمل UPDATE فقط
        if ($lastSubscription->end_date >= $today) {

            $lastSubscription->end_date = Carbon::parse($lastSubscription->end_date)
                ->addDays((int)$request->days);

            $lastSubscription->save();

            return response()->json([
                'message' => 'Subscription extended successfully',
                'subscription' => $lastSubscription
            ]);
        }

        // ✅ إذا الاشتراك منتهي → نعمل سجل جديد
        if ($request->renew_from === 'today') {

            $startDate = $today;
            $endDate   = $today->copy()->addDays((int)$request->days);
        } else {

            $startDate = $lastSubscription->end_date;
            $endDate   = Carbon::parse($lastSubscription->end_date)
                ->addDays((int)$request->days);
        }

        $subscription = Subscription::create([
            'player_id' => $player->id,
            'start_date' => $startDate,
            'end_date'  => $endDate
        ]);

        return response()->json([
            'message' => 'Subscription renewed successfully',
            'subscription' => $subscription
        ]);
    }
    public function current($id, Request $request)
    {
        $player = Player::where('id', $id)
            ->where('coach_id', $request->user()->id)
            ->firstOrFail();

        $subscription = $player->subscriptions()
            ->latest('end_date')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No subscription found'
            ], 404);
        }

        $today = Carbon::today();

        $daysLeft = $today->diffInDays($subscription->end_date, false);

        return response()->json([
            'subscription' => $subscription,
            'is_active' => $subscription->end_date >= $today,
            'days_left' => $daysLeft
        ]);
    }
}
