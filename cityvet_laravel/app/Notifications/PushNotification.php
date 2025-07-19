<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PushNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $data;
    protected $deviceToken;

    public function __construct($title, $body, $data = [], $deviceToken = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->deviceToken = $deviceToken;
    }

    public function via($notifiable)
    {
        return ['database', 'fcm'];
    }

    public function toArray($notifiable)
    {

        $stringData = [];
        foreach ($this->data as $key => $value) {
            $stringData[$key] = (string) $value;
        }

        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $stringData,
        ];
    }

    public function toFcm($notifiable)
    {

        $stringData = [];
        foreach ($this->data as $key => $value) {
            $stringData[$key] = (string) $value;
        }

        return [
            'device_token' => $this->deviceToken,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $stringData,
        ];
    }
} 