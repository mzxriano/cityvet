<?php

namespace App\Http\Controllers\Api;

use App\Models\Community;
use App\Models\CommunityComment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class CommunityCommentController extends Controller
{
    /**
     * Add a comment or reply to a post
     */
    public function store(Request $request, $communityId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:community_comments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $comment = CommunityComment::create([
            'community_id' => $communityId,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        // Optionally increment comments_count on the post
        Community::where('id', $communityId)->increment('comments_count');

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment->load('user'),
        ], 201);
    }

    /**
     * List comments (with nested replies) for a post
     */
    public function index($communityId)
    {
        $comments = CommunityComment::with(['user', 'children.user'])
            ->where('community_id', $communityId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($comments);
    }
} 