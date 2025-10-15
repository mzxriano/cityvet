<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PushNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $data;
    protected $deviceToken;
    protected $memoPath;

    public function __construct($title, $body, $data = [], $deviceToken = null, $memoPath = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->deviceToken = $deviceToken;
        $this->memoPath = $memoPath;
    }

    public function via($notifiable)
    {
        return ['database', 'fcm', 'mail'];
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
            'type' => 'activity_notification',
            'created_at' => now()->toISOString(),
            'recipient' => $notifiable->first_name . ' ' . $notifiable->last_name
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

    public function toMail($notifiable)
    {
        $activityDate = isset($this->data['activity_date']) ? $this->data['activity_date'] : 'soon';
        $activityTime = isset($this->data['activity_time']) ? $this->data['activity_time'] : '';
        $barangayName = isset($this->data['barangay_name']) ? $this->data['barangay_name'] : '';
        $category = isset($this->data['category']) ? ucfirst($this->data['category']) : 'Activity';
        $status = isset($this->data['status']) ? $this->data['status'] : 'scheduled';
        $details = isset($this->data['details']) ? $this->data['details'] : '';
        
        // Status-specific messaging
        switch ($status) {
            case 'up_coming':
                $statusText = 'Upcoming';
                $actionText = 'has been scheduled';
                $instruction = 'Please prepare your pets and bring necessary documents. Arrive 15 minutes early for registration.';
                $buttonText = 'View Activity Details';
                break;
            case 'on_going':
                $statusText = 'Ongoing';
                $actionText = 'is currently taking place';
                $instruction = 'You can still participate if you\'re in the area. Our veterinary team is ready to assist you.';
                $buttonText = 'Get Directions';
                break;
            case 'completed':
                $statusText = 'Completed';
                $actionText = 'has been completed successfully';
                $instruction = 'Thank you to everyone who participated. Check our app for upcoming activities in your area.';
                $buttonText = 'View Summary';
                break;
            case 'failed':
                $statusText = 'Cancelled';
                $actionText = 'has been cancelled';
                $instruction = 'We apologize for any inconvenience. We will notify you when this activity is rescheduled.';
                $buttonText = 'View Updates';
                break;
            default:
                $statusText = 'Scheduled';
                $actionText = 'has been scheduled';
                $instruction = 'Please check the details and prepare accordingly.';
                $buttonText = 'View Details';
        }
        
        $mail = (new MailMessage)
                    ->subject("🏥 CityVet: {$statusText} {$category} Activity in {$barangayName}")
                    ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . '!')
                    ->line("A {$category} activity {$actionText} in your area.")
                    ->line('')
                    ->line('**📋 Activity Information:**')
                    ->line('• **Type:** ' . $category . ' Service')
                    ->line('• **Purpose:** ' . $this->body)
                    ->line('• **Location:** ' . $barangayName . ' Barangay')
                    ->line('• **Date:** ' . $activityDate)
                    ->line('• **Time:** ' . $activityTime)
                    ->line('• **Status:** ' . ucfirst(str_replace('_', ' ', $status)));
        
        if ($details) {
            $mail->line('• **Additional Details:** ' . $details);
        }
        
        // Mention memo attachment if present
        if ($this->memoPath && \Storage::disk('public')->exists($this->memoPath)) {
            $mail->line('• **📎 Memo Attached:** Important information is attached to this email');
        }
        
        $mail->line('')
             ->line('**📝 Important Notes:**')
             ->line($instruction);
        
        if ($status === 'up_coming') {
            $mail->line('• Bring your pet\'s vaccination records')
                 ->line('• Ensure your pet is clean and well-behaved')
                 ->line('• Contact us if your pet has special medical conditions');
        }
        
        // Attach memo file if it exists
        if ($this->memoPath && \Storage::disk('public')->exists($this->memoPath)) {
            try {
                $fullPath = storage_path('app/public/' . $this->memoPath);
                $fileName = 'Activity_Memo_' . ($this->data['activity_id'] ?? 'memo') . '.' . pathinfo($this->memoPath, PATHINFO_EXTENSION);
                $mail->attach($fullPath, [
                    'as' => $fileName,
                    'mime' => \Storage::disk('public')->mimeType($this->memoPath),
                ]);
            } catch (\Exception $e) {
                // Log the error but don't fail the email sending
                \Log::error('Failed to attach memo to email notification: ' . $e->getMessage());
            }
        }
        
        return $mail;
    }
}