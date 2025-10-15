<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\User;
use App\Models\Admin;

class NotificationService
{
    /**
     * Create a notification for admin users
     */
    public static function notifyAdmin($title, $message, $type, $data = [])
    {
        // Get all admin users
        $admins = Admin::all();
        
        foreach ($admins as $admin) {
            AdminNotification::create([
                'admin_id' => $admin->id,
                'title' => $title,
                'body' => $message,
                'type' => $type,
                'read' => false,
                'data' => $data
            ]);
        }
    }

    /**
     * Notify admin about new user registration
     */
    public static function newUserRegistration($user)
    {
        self::notifyAdmin(
            'New User Registration',
            "A new user {$user->first_name} {$user->last_name} has registered from {$user->barangay}",
            'user_registration',
            [
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'barangay' => $user->barangay,
                'email' => $user->email
            ]
        );
    }

    /**
     * Notify admin about new animal registration
     */
    public static function newAnimalRegistration($animal)
    {
        $ownerName = $animal->owner ? $animal->owner->first_name . ' ' . $animal->owner->last_name : 'Unknown Owner';
        
        self::notifyAdmin(
            'New Animal Registration',
            "A new {$animal->type} named {$animal->name} has been registered by {$ownerName}",
            'animal_registration',
            [
                'animal_id' => $animal->id,
                'animal_name' => $animal->name,
                'animal_type' => $animal->type,
                'owner_name' => $ownerName,
                'owner_id' => $animal->owner_id
            ]
        );
    }

    /**
     * Notify admin about new activity schedule
     */
    public static function newActivitySchedule($activity)
    {
        $category = ucfirst($activity->category ?? 'Veterinary');
        $barangayName = $activity->barangay->name ?? 'Unknown Location';
        $activityDate = \Carbon\Carbon::parse($activity->date)->format('F j, Y');
        
        self::notifyAdmin(
            'New Activity Scheduled',
            "A new {$category} activity '{$activity->reason}' has been scheduled for {$barangayName} on {$activityDate}",
            'activity_schedule',
            [
                'activity_id' => $activity->id,
                'activity_category' => $activity->category,
                'activity_reason' => $activity->reason,
                'barangay_name' => $barangayName,
                'barangay_id' => $activity->barangay_id,
                'date' => $activity->date,
                'time' => $activity->time ?? null
            ]
        );
    }

    /**
     * Notify admin about new requested activity schedule (AEW)
     */
    public static function newRequestedActivitySchedule($activity)
    {
        $category = ucfirst($activity->category ?? 'Veterinary');
        $barangayName = $activity->barangay->name ?? 'Unknown Location';
        $activityDate = \Carbon\Carbon::parse($activity->date)->format('F j, Y');
        
        self::notifyAdmin(
            'New Activity was requested',
            "A new {$category} activity '{$activity->reason}' has been requested for {$barangayName} on {$activityDate}",
            'activity_schedule',
            [
                'activity_id' => $activity->id,
                'activity_category' => $activity->category,
                'activity_reason' => $activity->reason,
                'barangay_name' => $barangayName,
                'barangay_id' => $activity->barangay_id,
                'date' => $activity->date,
                'time' => $activity->time ?? null
            ]
        );
    }

    /**
     * Notify admin about low vaccine stock
     */
    public static function lowVaccineStock($vaccine, $currentStock, $threshold = 100)
    {
        if ($currentStock < $threshold) {
            self::notifyAdmin(
                'Low Vaccine Stock Alert',
                "{$vaccine->name} stock is running low. Only {$currentStock} doses remaining in inventory.",
                'stock_alert',
                [
                    'vaccine_id' => $vaccine->id,
                    'vaccine_name' => $vaccine->name,
                    'current_stock' => $currentStock,
                    'threshold' => $threshold
                ]
            );
        }
    }

    /**
     * Notify admin about new community post
     */
    public static function newCommunityPost($post)
    {
        $authorName = $post->user ? $post->user->first_name . ' ' . $post->user->last_name : 'Unknown User';
        
        self::notifyAdmin(
            'New Community Post',
            "A new post has been published by {$authorName} in the community forum",
            'community_post',
            [
                'post_id' => $post->id,
                'author_name' => $authorName,
                'author_id' => $post->user_id,
                'title' => $post->title ?? 'Untitled Post',
                'excerpt' => substr(strip_tags($post->content ?? ''), 0, 100) . '...'
            ]
        );
    }

    /**
     * Notify admin about new bite case report
     */
    public static function newBiteCaseReport($incident)
    {
        $victimName = $incident->victim_name ?? 'Unknown Victim';
        $location = $incident->barangay ?? 'Unknown Location';
        
        self::notifyAdmin(
            'New Bite Case Reported',
            "A new animal bite case has been reported involving {$victimName} in {$location}",
            'bite_case',
            [
                'incident_id' => $incident->id,
                'victim_name' => $victimName,
                'location' => $location,
                'incident_date' => $incident->incident_date ?? now(),
                'animal_type' => $incident->animal_type ?? 'Unknown Animal',
                'severity' => $incident->severity ?? 'Unknown'
            ]
        );
    }

    /**
     * Get unread notifications count for admin
     */
    public static function getUnreadCount($adminId)
    {
        return AdminNotification::where('admin_id', $adminId)
            ->where('read', false)
            ->count();
    }

    /**
     * Get recent notifications for admin
     */
    public static function getRecentNotifications($adminId, $limit = 5)
    {
        return AdminNotification::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId)
    {
        return AdminNotification::where('id', $notificationId)
            ->update(['read' => true]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($adminId)
    {
        return AdminNotification::where('admin_id', $adminId)
            ->where('read', false)
            ->update(['read' => true]);
    }
}
