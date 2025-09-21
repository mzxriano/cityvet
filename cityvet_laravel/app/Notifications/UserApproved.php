<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('CityVet Registration Application - Approved')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . ',')
            ->line('Congratulations! Your registration application for CityVet has been approved.')
            ->line('You can now log in to your account using your email address and the password that was sent to you during registration.')
            ->action('Login to CityVet', url('/login'))
            ->line('If you have forgotten your password, you can reset it using the "Forgot Password" link on the login page.')
            ->line('Welcome to CityVet!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $notifiable->id,
            'approved_at' => now(),
        ];
    }
}
