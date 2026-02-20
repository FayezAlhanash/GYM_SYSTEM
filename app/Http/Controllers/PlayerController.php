<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function store(Request $request)
    {
        $coachId = $request->user()->id;
        $request->validate([
            'full_name'   => 'required|string|max:255',
            'phone'       => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $player = Player::create([
            'coach_id'    => $coachId,
            'full_name'   => $request->full_name,
            'phone'       => $request->phone,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Player created successfully',
            'player'  => $player
        ], 201);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|integer|in:1,2,5'
        ]);

        $today = now();

        $query = Player::where('coach_id', $request->user()->id)
            ->with(['subscriptions' => function ($q) {
                $q->latest('end_date')->limit(1);
            }]);

        if (!empty($validated['filter'])) {

            $maxDate = $today->copy()->addDays($validated['filter']);

            $query->whereHas('subscriptions', function ($q) use ($today, $maxDate) {
                $q->whereDate('end_date', '>=', $today)
                    ->whereDate('end_date', '<=', $maxDate);
            });
        }

        $players = $query->paginate(10);

        // نحسب days_left لكل لاعب
        $players->getCollection()->each(function ($player) use ($today) {

            $lastSub = $player->subscriptions->first();

            if ($lastSub) {
                $player->days_left = $today->diffInDays($lastSub->end_date, false);
                $player->is_active = $lastSub->end_date >= $today;
            } else {
                $player->days_left = null;
                $player->is_active = false;
            }
        });

        return response()->json($players);
    }



    public function update(Request $request, $id)
    {
        $player = Player::where('id', $id)
            ->where('coach_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'full_name'   => 'sometimes|required|string|max:255',
            'phone'       => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $player->update($request->only([
            'full_name',
            'phone',
            'description'
        ]));

        return response()->json([
            'message' => 'Player updated successfully',
            'player'  => $player
        ]);
    }
    public function destroy(Request $request, $id)
    {
        $player = Player::where('id', $id)
            ->where('coach_id', $request->user()->id)
            ->firstOrFail();

        $player->delete();

        return response()->json([
            'message' => 'Player deleted successfully'
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);
        $keyword = $request->q;

        $players = Player::where('coach_id', $request->user()->id)
            ->where('full_name', 'LIKE', '%' . $keyword . '%')
            ->get();

        return response()->json($players);
    }

    public function expired(Request $request)
    {
        $today = now();

        $players = Player::where('coach_id', $request->user()->id)
            ->whereHas('subscriptions', function ($q) use ($today) {
                $q->whereDate('end_date', '<', $today);
            })
            ->with(['subscriptions' => function ($q) {
                $q->latest('end_date')->limit(1);
            }])
            ->get();

        return response()->json($players);
    }
    public function dashboard(Request $request)
    {
        $coachId = $request->user()->id;
        $today = now();

        // كل اللاعبين تبع المدرب
        $players = Player::where('coach_id', $coachId)->get();

        $totalPlayers = $players->count();

        $expired = 0;
        $lessThanFive = 0;
        $active = 0;

        foreach ($players as $player) {

            $lastSub = $player->subscriptions()
                ->latest('end_date')
                ->first();

            if (!$lastSub) {
                continue;
            }

            $daysLeft = $today->diffInDays($lastSub->end_date, false);

            if ($daysLeft < 0) {
                $expired++;
            } else {
                $active++;
            }

            if ($daysLeft >= 0 && $daysLeft <= 5) {
                $lessThanFive++;
            }
        }

        return response()->json([
            'total_players' => $totalPlayers,
            'active_players' => $active,
            'expired_players' => $expired,
            'less_than_5_days' => $lessThanFive
        ]);
    }
}
