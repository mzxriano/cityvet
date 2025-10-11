<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('admin')->user();
        
        if (!$user) {
            return redirect()->route('showLogin');
        }

        $notifications = \App\Models\AdminNotification::where('admin_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications', compact('notifications'));
    }

    public static function getNotificationColor($type)
    {
        return match($type) {
            'user_registration' => 'bg-green-100 dark:bg-green-900',
            'animal_registration' => 'bg-green-100 dark:bg-green-900',
            'activity_schedule' => 'bg-blue-100 dark:bg-blue-900',
            'stock_alert' => 'bg-red-100 dark:bg-red-900',
            'community_post' => 'bg-purple-100 dark:bg-purple-900',
            'bite_case' => 'bg-orange-100 dark:bg-orange-900',
            default => 'bg-gray-100 dark:bg-gray-700'
        };
    }

    public static function getBadgeColor($type)
    {
        return match($type) {
            'user_registration' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'animal_registration' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'activity_schedule' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'stock_alert' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'community_post' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            'bite_case' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
        };
    }

    public static function getNotificationIcon($type)
    {
        $iconClass = match($type) {
            'user_registration' => 'text-green-600 dark:text-green-400',
            'animal_registration' => 'text-green-600 dark:text-green-400',
            'activity_schedule' => 'text-blue-600 dark:text-blue-400',
            'stock_alert' => 'text-red-600 dark:text-red-400',
            'community_post' => 'text-purple-600 dark:text-purple-400',
            'bite_case' => 'text-orange-600 dark:text-orange-400',
            default => 'text-gray-600 dark:text-gray-400'
        };

        return match($type) {
            'user_registration', 'animal_registration' => '<svg class="w-5 h-5 ' . $iconClass . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>',
            'activity_schedule' => '<svg class="w-5 h-5 ' . $iconClass . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'stock_alert' => '<svg class="w-5 h-5 ' . $iconClass . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
            'community_post' => '<svg class="w-5 h-5 ' . $iconClass . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8V4l4 4z"></path></svg>',
            'bite_case' => '<svg class="w-5 h-5 ' . $iconClass . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
            default => '<svg class="w-5 h-5 ' . $iconClass . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>'
        };
    }

    public function getRecentNotifications()
    {
        $user = Auth::guard('admin')->user();
        
        if (!$user) {
            return response()->json(['notifications' => [], 'count' => 0]);
        }

        $notifications = NotificationService::getRecentNotifications($user->id, 5);
        $unreadCount = NotificationService::getUnreadCount($user->id);

        return response()->json([
            'notifications' => $notifications,
            'count' => $unreadCount
        ]);
    }

    public function markAsRead($id)
    {
        NotificationService::markAsRead($id);
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = Auth::guard('admin')->user();
        
        if ($user) {
            NotificationService::markAllAsRead($user->id);
        }

        return response()->json(['success' => true]);
    }
}
