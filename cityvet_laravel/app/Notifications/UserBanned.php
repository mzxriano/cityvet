<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserBanned extends Notification implements ShouldQueue
{
    use Queueable;

    public $banReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $banReason)
    {
        $this->banReason = $banReason;
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
            ->subject('Account Suspended - CityVet')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . ',')
            ->line('We regret to inform you that your account has been suspended.')
            ->line('**Reason for suspension:** ' . $this->banReason)
            ->line('Your access to the CityVet system has been temporarily restricted.')
            ->line('If you believe this is an error or would like to appeal this decision, please contact our support team.')
            ->action('Contact Support', url('/contact'))
            ->line('Thank you for your understanding.')
            ->salutation('Best regards, The CityVet Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ban_reason' => $this->banReason,
            'banned_at' => now(),
        ];
    }
}
