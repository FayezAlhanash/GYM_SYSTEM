<?php

namespace App\Services;

use Kreait\Firebase\Factory; // 🔥 هذا كان ناقص
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FirebaseService
{
    public function send($token, $title, $body)
{
    try {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/gymfy-11b38-firebase-adminsdk-fbsvc-202a685d45.json'));

        $messaging = $factory->createMessaging();

        $message = CloudMessage::new()
            ->withNotification(FirebaseNotification::create($title, $body))
            ->toToken($token);

        $messaging->send($message);

    } catch (\Exception $e) {
        dd($e->getMessage());
    }
}
}
