<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRejected extends Notification
{
    use Queueable;

    private $rejectionMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $rejectionMessage)
    {
        $this->rejectionMessage = $rejectionMessage;
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
            ->subject('CityVet Registration Application - Rejected')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . ',')
            ->line('We regret to inform you that your registration application for CityVet has been rejected.')
            ->line('**Reason for rejection:**')
            ->line($this->rejectionMessage)
            ->line('If you believe this was an error or would like to reapply, please contact our support team.')
            ->line('Thank you for your interest in CityVet.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'rejection_message' => $this->rejectionMessage,
            'user_id' => $notifiable->id,
        ];
    }
}
