<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'type', 'read', 'data'];
    public function user() {
        return $this->belongsTo(User::class);
    }
} 