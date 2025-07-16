<?php

namespace App\Http\Controllers\Api;

use App\Mail\VerifyEmail;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register newly created user
     */
    public function register(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'barangay_id' => 'required|exists:barangays,id',
            'street' => 'required|string|max:255',
            'phone_number' => 'required|string|size:11|unique:users,phone_number',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        // Return all validation errors as JSON
        if ($validator->fails()) 
        {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors() 
            ], 422);
        }

        $validated = $validator->validate();

        $roleId = Role::where('name','Owner')->first()->id;

        $user = User::create([
            ...$validated, 
            'role_id' => $roleId, 
            'password' => Hash::make($validated['password'])
        ]);

        try {
            Mail::to($user->email)->send(new VerifyEmail($user));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User created but failed to send verification email'
            ], 500);
        }

        return response()->json(['message' => 'User successfully registered!']);

    }

    /**
     * Register newly created user
     */
    public function login(Request $request)
    {

        $validator = validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) 
        {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validate();
        
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'error' => 'user_not_found',
            ], 404);
        }

        if(!$user->hasVerifiedEmail()) 
        {
            return response()->json([
                'message' => 'Email is not verified. Please check your inbox.',
                'error' => 'email_not_verified',
            ], 400);
        }

        $credentials = request(['email', 'password']);

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid Credentials' ,'error' => 'invalid_validator'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }

    public function verifyEmail($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified'
            ]);
        }

        $user->markEmailAsVerified();

        return redirect()->route('email.successful')->with('success', 'Email verified successfully! You can now login to the app.');
    }

    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Valid email is required'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        try {
            Mail::to($user->email)->send(new VerifyEmail($user));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email'
            ], 500);
        }

        return redirect()->route('email.successful')->with('success', 'Email verified successfully! You can now login to the app.');
    }

    public function logout(Request $request)
    {
        try {
            // Invalidate the current token
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }



}
