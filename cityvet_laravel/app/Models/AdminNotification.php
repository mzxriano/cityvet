<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';
    protected $fillable = ['admin_id', 'title', 'body', 'type', 'read', 'data'];
    
    protected $casts = [
        'read' => 'boolean',
        'data' => 'array',
    ];
    
    public function admin() {
        return $this->belongsTo(Admin::class);
    }
} 