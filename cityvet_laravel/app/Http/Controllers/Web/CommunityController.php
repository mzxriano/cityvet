<?php

namespace App\Http\Controllers\Web;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommunityController extends Controller
{
    // Return all pending posts as JSON for the admin panel
    public function pending()
    {
        $posts = Community::with(['user', 'images'])
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
        $posts = Community::with(['user', 'images'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }
} 