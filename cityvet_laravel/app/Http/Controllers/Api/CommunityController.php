<?php

namespace App\Http\Controllers\Api;

use App\Models\Community;
use App\Models\CommunityImage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary;

class CommunityController extends Controller
{
    private function getCloudinary()
    {
        return new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
                'secure' => env('CLOUDINARY_SECURE', true),
            ],
        ]);
    }

    /**
     * List all community posts with images, user, comments/likes count
     */
    public function index()
    {
        $posts = Community::with(['user', 'images', 'comments', 'likes'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();
        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'status' => $post->status,
                'user' => $post->user ? [
                    'id' => $post->user->id,
                    'first_name' => $post->user->first_name,
                    'last_name' => $post->user->last_name,
                    'image_url' => $post->user->image_url,
                ] : null,
                'images' => $post->images->map(function ($img) {
                    return [
                        'image_url' => $img->image_url,
                        'image_public_id' => $img->image_public_id,
                    ];
                }),
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });
        return response()->json($posts);
    }

    /**
     * Show a single post with images, comments, likes
     */
    public function show($id)
    {
        $post = Community::with(['user', 'images', 'comments.children', 'likes'])
            ->findOrFail($id);
        return response()->json($post);
    }

    /**
     * Create a new community post (with multiple images)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,heic|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $post = Community::create([
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $cloudinary = $this->getCloudinary();
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $image->getPathname(),
                        [
                            'folder' => 'community',
                            'transformation' => [
                                'width' => 800,
                                'height' => 600,
                                'crop' => 'limit',
                                'quality' => 'auto',
                                'fetch_format' => 'auto'
                            ]
                        ]
                    );
                    CommunityImage::create([
                        'community_id' => $post->id,
                        'image_url' => $uploadResult['secure_url'],
                        'image_public_id' => $uploadResult['public_id'],
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Post created successfully.',
            'post' => $post->load(['images', 'user']),
        ], 201);
    }

    /**
     * Update a community post (content and new images)
     */
    public function update(Request $request, $id)
    {
        // Debug: Log the incoming request
        \Log::info('Update post request', [
            'id' => $id,
            'all_data' => $request->all(),
            'content' => $request->input('content'),
            'has_files' => $request->hasFile('images'),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
        ]);

        $post = Community::with(['images', 'user'])->findOrFail($id);

        // Only the owner can update
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,heic|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $post->content = $request->content;
        $post->save();

        // Handle new image uploads (additive)
        if ($request->hasFile('images')) {
            $cloudinary = $this->getCloudinary();
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $image->getPathname(),
                        [
                            'folder' => 'community',
                            'transformation' => [
                                'width' => 800,
                                'height' => 600,
                                'crop' => 'limit',
                                'quality' => 'auto',
                                'fetch_format' => 'auto'
                            ]
                        ]
                    );
                    CommunityImage::create([
                        'community_id' => $post->id,
                        'image_url' => $uploadResult['secure_url'],
                        'image_public_id' => $uploadResult['public_id'],
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Post updated successfully.',
            'post' => $post->load(['images', 'user']),
        ]);
    }

    /**
     * Get pending posts for admin review
     */
    public function getPendingPosts()
    {
        $posts = Community::with(['user', 'images'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'status' => $post->status,
                'user' => $post->user ? [
                    'id' => $post->user->id,
                    'first_name' => $post->user->first_name,
                    'last_name' => $post->user->last_name,
                    'image_url' => $post->user->image_url,
                ] : null,
                'images' => $post->images->map(function ($img) {
                    return [
                        'image_url' => $img->image_url,
                        'image_public_id' => $img->image_public_id,
                    ];
                }),
                'created_at' => $post->created_at,
            ];
        });
        
        return response()->json($posts);
    }

    /**
     * Approve or reject a post (admin only)
     */
    public function reviewPost(Request $request, $id)
    {
        $post = Community::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $post->status = $request->status;
        $post->save();

        return response()->json([
            'message' => 'Post ' . $request->status . ' successfully.',
            'post' => $post->load(['images', 'user']),
        ]);
    }

    /**
     * Get current user's posts (including pending ones)
     */
    public function getUserPosts()
    {
        $posts = Community::with(['user', 'images', 'comments', 'likes'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'status' => $post->status,
                'user' => $post->user ? [
                    'id' => $post->user->id,
                    'first_name' => $post->user->first_name,
                    'last_name' => $post->user->last_name,
                    'image_url' => $post->user->image_url,
                ] : null,
                'images' => $post->images->map(function ($img) {
                    return [
                        'image_url' => $img->image_url,
                        'image_public_id' => $img->image_public_id,
                    ];
                }),
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });
        return response()->json($posts);
    }

    /**
     * Delete a community post (and its images, comments, likes)
     */
    public function destroy($id)
    {
        $post = Community::with(['images', 'comments', 'likes'])->findOrFail($id);

        // Only the owner can delete
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete images from Cloudinary
        $cloudinary = $this->getCloudinary();
        foreach ($post->images as $image) {
            if ($image->image_public_id) {
                try {
                    $cloudinary->uploadApi()->destroy($image->image_public_id);
                } catch (\Exception $e) {
                    // Log but continue
                    \Log::error('Failed to delete image from Cloudinary: ' . $e->getMessage());
                }
            }
        }

        // Delete related images, comments, likes
        $post->images()->delete();
        $post->comments()->delete();
        $post->likes()->delete();

        // Delete the post itself
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully.']);
    }

    /**
     * Report a community post
     */
    public function reportPost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post = Community::findOrFail($id);
        
        // Update post as reported
        $post->reported = true;
        $post->save();

        return response()->json(['message' => 'Post reported successfully.']);
    }

    /**
     * Report a community comment
     */
    public function reportComment(Request $request, $commentId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment = \App\Models\CommunityComment::findOrFail($commentId);
        
        // Update comment as reported
        $comment->reported = true;
        $comment->save();

        return response()->json(['message' => 'Comment reported successfully.']);
    }
} 