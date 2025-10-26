<?php

namespace App\Services\Api;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Notifications\PushNotification;

class ActivityService
{
    /**
     * Handles the creation of a new activity request and syncs barangays (many-to-many).
     *
     * @param array $data Validated request data (including 'barangay_ids').
     * @param User $user The authenticated user submitting the request.
     * @return Activity
     * @throws \Exception
     */
    public function createActivityRequest(array $data, User $user): Activity
    {
        // 1. Handle file uploads
        $memoFiles = $data['memos'] ?? [];
        $memoPaths = $this->handleMemosUpload($memoFiles);
        $memoValue = $this->formatMemoValue($memoPaths);

        // 2. Create the activity record
        $activity = Activity::create([
            'reason' => $data['reason'],
            'details' => $data['details'],
            'date' => $data['date'],
            'time' => $data['time'],
            'status' => 'pending', 
            'created_by' => $user->id,
            'category' => $data['category'],
            'memo' => $memoValue,
        ]);

        // 3. CRITICAL: Sync Barangays to the PIVOT TABLE (Many-to-Many)
        $barangayIds = $this->getBarangayIdsFromInput($data['barangay_ids']);
        $activity->barangays()->sync($barangayIds);
        
        // 4. Send notification (Ensure NotificationService class is available)
        NotificationService::newRequestedActivitySchedule($activity); 

        Log::info('Activity request submitted successfully', ['activity_id' => $activity->id]);

        return $activity;
    }

    /**
     * Utility to convert memo string back to an array for the response.
     */
    public function getMemoPaths(?string $memoValue): array
    {
        if (empty($memoValue)) {
            return [];
        }
        $decoded = json_decode($memoValue, true);
        return is_array($decoded) ? $decoded : [$memoValue];
    }
    
    // --- Internal Utility Methods ---
    
    protected function getBarangayIdsFromInput(string $input): array
    {
        return collect(explode(',', $input))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->all();
    }
    
    protected function handleMemosUpload(array $memoFiles): array
    {
        $memoPaths = [];
        foreach ($memoFiles as $memoFile) {
            if ($memoFile instanceof UploadedFile && $memoFile->isValid()) {
                $path = $memoFile->store('activity_memos', 'public');
                $memoPaths[] = $path;
            }
        }
        return $memoPaths;
    }

    protected function formatMemoValue(array $memoPaths): ?string
    {
        if (empty($memoPaths)) {
            return null;
        }
        return count($memoPaths) > 1 ? json_encode($memoPaths) : $memoPaths[0];
    }

    /**
     * Handles the approval of a pending activity, updates status, and sends notifications.
     *
     * @param Activity $activity
     * @param Request $request (or just notifyUsers boolean)
     * @param int $adminId
     * @return void
     */
    public function approveActivity(Activity $activity, bool $notifyUsers, int $adminId): void
    {
        if ($activity->status !== 'pending') {
            throw new \Exception('This activity is no longer pending approval.');
        }

        $activity->update([
            'status' => 'up_coming',
            'approved_at' => now(),
            'approved_by' => $adminId
        ]);

        if ($activity->creator) {
            $activity->creator->notify(new PushNotification(
                'Activity Request Approved',
                "Your activity request '{$activity->reason}' has been approved and scheduled for " . $activity->date->format('M d, Y') . " at " . $activity->time->format('h:i A'),
                ['activity_id' => $activity->id, 'type' => 'activity_approved']
            ));
        }

        if ($notifyUsers) {
            $barangayIds = $activity->barangays->pluck('id')->toArray();
            
            $barangayNames = $activity->barangays->pluck('name')->implode(', ');

            $usersToNotify = User::where('status', '!=', 'rejected')
                ->whereIn('barangay_id', $barangayIds) 
                ->get();

            foreach ($usersToNotify as $user) {
                $user->notify(new PushNotification(
                    'New Activity Scheduled',
                    "A new {$activity->category} activity has been scheduled for {$barangayNames} on " . $activity->date->format('M d, Y') . " at " . $activity->time->format('h:i A'),
                    ['activity_id' => $activity->id, 'type' => 'new_activity']
                ));
            }
        }
    }
}