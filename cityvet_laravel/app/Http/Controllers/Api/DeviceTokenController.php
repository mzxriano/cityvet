<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class DeviceTokenController extends Controller
{
    public function save(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'device_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        $token = $request->device_token;
        $deviceType = $request->device_type;

        // Save or update the device token
        $user->deviceTokens()->updateOrCreate(
            ['token' => $token],
            ['device_type' => $deviceType]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token saved.',
        ]);
    }
} 