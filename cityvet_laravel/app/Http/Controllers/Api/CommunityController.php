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
            ->orderBy('created_at', 'desc')
            ->get();
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
} 