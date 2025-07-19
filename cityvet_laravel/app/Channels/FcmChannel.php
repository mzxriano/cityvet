<?php

namespace App\Channels;

use Illuminate\Broadcasting\Channel;
use Illuminate\Notifications\Notification;
use App\Services\FcmV1Service;

class FcmChannel 
{
    protected $fcm;

    public function __construct(FcmV1Service $fcm)
    {
        $this->fcm = $fcm;
    }

    public function send($notifiable, Notification $notification)
    {

        \Log::info('FCMChannel send called', ['notifiable_id' => $notifiable->id]);
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $data = $notification->toFcm($notifiable);

        // Loop through all device tokens for the user
        foreach ($notifiable->deviceTokens as $deviceToken) {
            $this->fcm->send(
                $deviceToken->token,
                $data['title'] ?? '',
                $data['body'] ?? '',
                $data['data'] ?? []
            );
        }
    }
} 