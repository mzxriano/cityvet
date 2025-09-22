<?php

namespace App\Http\Controllers\Web;

use App\Models\Community;
use App\Models\CommunityComment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommunityController extends Controller
{
    // Return all pending posts as JSON for the admin panel
    public function pending()
    {
        $posts = Community::with(['user', 'images', 'comments.user', 'likes.user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }

    // Approve or reject a post
    public function review(Request $request, $id)
    {
        $post = Community::findOrFail($id);
        $request->validate(['status' => 'required|in:approved,rejected']);
        $post->status = $request->status;
        $post->save();

        return response()->json(['message' => 'Post ' . $request->status . ' successfully.']);
    }

    // Return all approved posts as JSON for the admin panel
    public function approved()
    {
        $posts = Community::with(['user', 'images', 'comments.user', 'likes.user'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }

    // Return all reported posts
    public function reportedPosts()
    {
        $reportedPosts = Community::with(['user', 'images'])
            ->where('reported', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reportedPosts);
    }

    // Return all reported comments
    public function reportedComments()
    {
        $reportedComments = CommunityComment::with(['user', 'community'])
            ->where('reported', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reportedComments);
    }

    // Report a post
    public function reportPost(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $post = Community::findOrFail($id);
        
        // Update post as reported
        $post->reported = true;
        $post->save();

        // You can also create a separate reports table record here if needed
        // Report::create([
        //     'user_id' => auth()->id(),
        //     'reportable_type' => Community::class,
        //     'reportable_id' => $id,
        //     'reason' => $request->reason
        // ]);

        return response()->json(['message' => 'Post reported successfully.']);
    }

    // Report a comment
    public function reportComment(Request $request, $commentId)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $comment = CommunityComment::findOrFail($commentId);
        
        // Update comment as reported
        $comment->reported = true;
        $comment->save();

        return response()->json(['message' => 'Comment reported successfully.']);
    }
} 