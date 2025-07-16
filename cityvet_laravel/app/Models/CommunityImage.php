<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id', 'image_url', 'image_public_id'
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }
} 