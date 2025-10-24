<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use App\Notifications\PushNotification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ActivityCreationService
{
    public function createActivityAndNotify(array $validatedData, array $memoPaths, array $selectedBarangays): Activity
    {
        $memoPath = count($memoPaths) > 1 ? json_encode($memoPaths) : ($memoPaths[0] ?? null);

        $activity = Activity::create([
            'reason' => $validatedData['reason'],
            'category' => $validatedData['category'],
            'details' => $validatedData['details'], 
            'time' => $validatedData['time'],
            'date' => $validatedData['date'],
            'status' => $validatedData['status'],
            'memo' => $memoPath
        ]);

        $activity->barangays()->attach($selectedBarangays);

        NotificationService::newActivitySchedule($activity);

        $this->sendActivityNotifications($activity, $selectedBarangays);

        return $activity;
    }

    protected function sendActivityNotifications(Activity $activity, array $barangayIds): void
    {
        $users = User::whereIn('barangay_id', $barangayIds)
                     ->where('status', '!=', 'rejected')
                     ->get();

        // Load necessary relationships for notification content
        $activity->load('barangays');

        foreach ($users as $user) {
            // Logic for notification content (moved from Controller)
            $activityDate = Carbon::parse($activity->date)->format('F j, Y');
            $activityTime = Carbon::parse($activity->time)->format('h:i A');
            
            // Generate a list of barangay names for the body
            $barangayNames = $activity->barangays->pluck('name')->implode(', ');

            [$icon, $actionText, $instruction] = $this->getNotificationStatusDetails($activity->status);
            
            $category = ucfirst($activity->category ?? 'Veterinary');
            $notificationTitle = "{$icon} CityVet: {$category} Activity Update";
            $notificationBody = "A {$category} activity '{$activity->reason}' {$actionText} in {$barangayNames} on {$activityDate} at {$activityTime}. {$instruction}";

            if ($activity->memo) {
                Log::info("Sending notification with memo attachment: {$activity->memo} for activity {$activity->id}");
            }
            
            $user->notify(new PushNotification(
                $notificationTitle,
                $notificationBody,
                [
                    'activity_id' => $activity->id,
                    'activity_date' => $activityDate,
                    'activity_time' => $activityTime,
                    'barangay_name' => $barangayName,
                    'category' => $activity->category,
                    'reason' => $activity->reason,
                    'status' => $activity->status,
                    'details' => $activity->details ?? ''
                ],
                null, // device token
                $activity->memo // memo file path
            ));
        }
    }
    
    // Extracted logic for status details
    protected function getNotificationStatusDetails(string $status): array
    {
        switch ($activity->status) {
            case 'up_coming':
                $icon = '📅';
                $actionText = 'scheduled';
                $instruction = 'Please prepare your pets and bring necessary documents.';
                break;
            case 'on_going':
                $icon = '🏥';
                $actionText = 'is currently ongoing';
                $instruction = 'You can still participate if you\'re in the area.';
                break;
            case 'completed':
                $icon = '✅';
                $actionText = 'has been completed';
                $instruction = 'Thank you to everyone who participated.';
                break;
            case 'failed':
                $icon = '❌';
                $actionText = 'has been cancelled';
                $instruction = 'We apologize for any inconvenience. Please stay tuned for rescheduling information.';
                break;
            default:
                $icon = '🏥';
                $actionText = 'has been scheduled';
                $instruction = 'Please check the details and prepare accordingly.';
        }
    }
}