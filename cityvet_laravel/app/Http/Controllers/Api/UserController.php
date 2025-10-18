<?php

namespace App\Http\Controllers\Api;

use Cloudinary\Cloudinary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.*', 'roles.name as role_name')
            ->get();


        return view("users", compact("users"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $user = auth()->user()->load(['barangay', 'roles']);

        \Log::info("User role: {$user->roles->pluck('name')->first()}");

        return response()->json([
            "user" => [
                "id" => $user->id,
                "role" => $user->currentRole->name ?? $user->roles->pluck('name')->first(),
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "suffix" => $user->suffix,
                "email" => $user->email,
                "phone_number" => $user->phone_number,
                "birth_date" => $user->birth_date,
                "street" => $user->street,
                "barangay" => $user->barangay ?? null,
                "image_url" => $user->image_url,
                "image_public_id" => $user->image_public_id,
                "force_password_change" => $user->force_password_change,
                "status" => $user->status,
            ]
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request)
    {

        \Log::info('Edit endpoint hit');
        \Log::info('Update request received', [
        'method' => $request->method(),
        'content_type' => $request->header('Content-Type'),
        'has_file' => $request->hasFile('image'),
        'all_data' => $request->all(),
        'files' => $request->allFiles(),
    ]);
        $user = auth()->user();

        $validator = Validator::make($request->all(), rules: [
            'first_name'   => 'sometimes|string|max:100',
            'last_name'    => 'sometimes|string|max:100',
            'suffix'       => 'sometimes|nullable|string|max:50',
            'email'        => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|string|unique:users,phone_number,' . $user->id,
            'birth_date'   => 'sometimes|date',
            'barangay_id'  => 'sometimes|integer|exists:barangays,id',
            'street'       => 'sometimes|nullable|string',
            'image'        => 'sometimes|nullable|image|mimes:jpg,png,jpeg,webp,heic|max:2048',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validate();

        unset($validated['image']);

        if($request->hasFile('image') && $request->file('image')->isValid()){

            try {
                $cloudinary = $this->getCloudinary();

                if ($user->image_public_id) {
                    \Log::info('Deleting old image', ['public_id' => $user->image_public_id]);
                    $cloudinary->uploadApi()->destroy($user->image_public_id);
                }

                $uploadResult = $cloudinary->uploadApi()->upload($request->file('image')->getPathname(), [
                
                    'folder' => 'users',
                    'transformation' => [
                        'width' => 800,
                        'height' => 600,
                        'crop' => 'limit',
                        'quality' => 'auto',
                        'fetch_format' => 'auto'
                    ]
                
                ]);

                \Log::info('Image uploaded successfully', ['result' => $uploadResult]);

                $validated['image_url'] = $uploadResult['secure_url'];
                $validated['image_public_id'] = $uploadResult['public_id'];
            } catch (\Exception $e) {
                 \Log::error('Image upload failed', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Image upload failed.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => [
                'first_name'=> $user->first_name,
                'last_name'=> $user->last_name,
                'suffix' => $user->suffix,
                'role' => $user->currentRole->name ?? $user->roles->pluck('name')->first(),
                'email'=> $user->email,
                'phone_number'=> $user->phone_number,
                'birth_date' => $user->birth_date,
                'barangay' => [
                    'id' => $user->barangay->id,
                    'name' => $user->barangay->name,
                ],
                'street'=> $user->street,
                'image_url' => $user->image_url,
                'image_public_id' => $user->image_public_id,
            ]
        ], 200);
    }

    /**
     * Fetch aew users.
     */
    public function fetchAew()
    {
        try {
            $aewUsers = DB::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->leftJoin('barangays', 'users.barangay_id', '=', 'barangays.id')
                ->where('roles.name', 'aew')
                ->select([
                    'users.id',
                    'users.first_name',
                    'users.last_name',
                    'users.suffix',
                    'users.email',
                    'users.phone_number',
                    'users.created_at',
                    'roles.name as role_name',
                    'barangays.name as barangay_name'
                ])
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'position' => $user->role_name ?? 'N/A',
                        'barangay' => $user->barangay_name ?? 'N/A',
                        'contact' => $user->phone_number,
                        'email' => $user->email,
                        'specialization' => 'Agricultural Extension Worker',
                    ];
                });

            return response()->json([
                'success' => true,
                'aew_users' => $aewUsers
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching AEW users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching AEW users',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function edit(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->password = bcrypt($request->password);
            $user->force_password_change = false;
            $user->save();

            return response()->json([
                'message' => 'Password changed successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Password change failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to change password',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }


}
