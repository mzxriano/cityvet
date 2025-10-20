<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'body',
        'type',
        'read',
        'data',
    ];

    protected $casts = [
        'read' => 'boolean',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the admin that owns the notification
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Scope to get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope to get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read' => true]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update(['read' => false]);
    }
}
