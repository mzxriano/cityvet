<?php

namespace App\Http\Controllers\Api;

use App\Models\Community;
use App\Models\CommunityLike;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommunityLikeController extends Controller
{
    /**
     * Like or unlike a post
     */
    public function toggle($communityId)
    {
        $userId = auth()->id();
        $like = CommunityLike::where('community_id', $communityId)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            // Unlike
            $like->delete();
            Community::where('id', $communityId)->decrement('likes_count');
            return response()->json(['message' => 'Post unliked.']);
        } else {
            // Like
            CommunityLike::create([
                'community_id' => $communityId,
                'user_id' => $userId,
            ]);
            Community::where('id', $communityId)->increment('likes_count');
            return response()->json(['message' => 'Post liked.']);
        }
    }
} 