<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Coach;
use App\Services\FirebaseService;
use Carbon\Carbon;
use App\Models\Notification;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'app:check-expired-subscriptions';
    protected $description = 'Check expired subscriptions and notify coach';

    public function handle()
    {
        $today = Carbon::today();

        echo "Today: " . $today . "\n";

        // 🧪 نجيب كل الاشتراكات ونطبعها (Debug)
        $allSubs = Subscription::with('player')->get();

        echo "All subscriptions:\n";
        foreach ($allSubs as $sub) {
            echo $sub->player->name . " | End: " . $sub->end_date . "\n";
        }

        echo "----------------------\n";

        $expiredSubs = Subscription::with('player')
            ->whereDate('end_date', '<', $today)
            ->get();

        if ($expiredSubs->isEmpty()) {
            echo "No expired subscriptions today.\n";
            return;
        }

        $grouped = $expiredSubs->groupBy(function ($sub) {
            return $sub->player->coach_id;
        });

        $firebase = new FirebaseService();

        foreach ($grouped as $coachId => $subs) {

            $coach = Coach::find($coachId);

            if (!$coach || !$coach->fcm_token) {
                echo "Coach {$coachId} has no token\n";
                continue;
            }

            $names = $subs->map(function ($sub) {
                return optional($sub->player)->name;
            })->filter()->toArray();

            $message = implode(', ', $names);

            echo "Sending to Coach {$coach->id}: {$message}\n";

            $firebase->send(
                $coach->fcm_token,
                'Finished Subscriptions',
                "Players: " . $message
            );
            Notification::create([
                'coach_id' => $coach->id,
                'players' => $message,
                'date' => now()->toDateString(),
            ]);
        }

        return 0;
    }
}
