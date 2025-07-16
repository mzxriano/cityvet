<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'content', 'likes_count', 'comments_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(CommunityImage::class);
    }

    public function comments()
    {
        return $this->hasMany(CommunityComment::class);
    }

    public function likes()
    {
        return $this->hasMany(CommunityLike::class);
    }
}
