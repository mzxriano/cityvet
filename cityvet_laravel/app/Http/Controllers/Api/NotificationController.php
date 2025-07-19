<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Notifications\PushNotification;
use App\Models\User;

class NotificationController extends Controller
{
    public function sendPushNotification(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'sometimes|array',
            'user_id' => 'required|integer',
        ]);

        $deviceToken = $request->input('device_token');
        $title = $request->input('title');
        $body = $request->input('body');
        $data = $request->input('data', []);
        $userId = $request->input('user_id');

        $user = User::findOrFail($userId);
        $user->notify(new PushNotification($title, $body, $data, $deviceToken));

        return response()->json(['message' => 'Notification sent.']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? '',
                'body' => $notification->data['body'] ?? '',
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at,
            ];
        });
        return response()->json($notifications);
    }

    public function markAsRead($id, Request $request)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }
} 