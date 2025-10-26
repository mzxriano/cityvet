<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AewRequestApproved extends Notification
{
    use Queueable;

     protected $title;
     protected $data;
     protected $deviceToken;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $data = [], $deviceToken = null)
    {
        $this->title = $title;
        $this->data = $data;
        $this->deviceToken = $deviceToken;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'fcm', 'mail'];
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
            'data' => $stringData,
            'android' => [
                'notification' => [
                    'icon' => 'ic_notification',
                    'color' => '#2563eb',
                    'sound' => 'default',
                    'channel_id' => 'cityvet_activities'
                ]
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $notifiable->id
        ];
    }
}
