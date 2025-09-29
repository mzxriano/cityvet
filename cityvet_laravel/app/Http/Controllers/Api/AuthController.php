<?php

namespace App\Http\Controllers\Api;

use App\Mail\VerifyEmail;
use App\Models\Role;
use App\Models\User;
use App\Notifications\PushNotification;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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
            'suffix' => 'nullable|string|max:50',
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

        $role = Role::where('name','pet_owner')->first();

        $user = User::create([
            ...$validated, 
            'password' => Hash::make($validated['password'])
        ]);

       $user->roles()->attach($role->id); 

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

        // Check user status before allowing login
        if ($user->status === 'banned') {
            return response()->json([
                'message' => 'Your account has been banned. Please contact support.',
                'error' => 'banned_account',
            ], 403);
        }

        // Check if email is verified
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
                return response()->json(['message' => 'Invalid Credentials' ,'error' => 'invalid_credentials'], 400);
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

        return redirect()->route('email.successful')->with('success', 'Verification email sent successfully!');
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

        return redirect()->route('email.successful')->with('success', 'Verification email sent successfully!');
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

    /**
     * Handle forgot password request: generate OTP and email it to user
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        $otp = rand(1000, 9999); 
        $expiresAt = now()->addMinutes(10);

        // Store OTP (hashed) in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send OTP via email
        Mail::raw("Your CityVet password reset OTP is: $otp\nThis code will expire in 10 minutes.", function ($message) use ($email) {
            $message->to($email)
                ->subject('CityVet Password Reset OTP');
        });

        return response()->json(['message' => 'OTP sent to your email.'], 200);
    }

    /**
     * Handle password reset: verify OTP and update password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!$record) {
            return response()->json(['message' => 'No OTP request found for this email.'], 404);
        }

        // Check if OTP is expired (10 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 400);
        }

        // Verify OTP
        if (!Hash::check($request->otp, $record->token)) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the OTP record
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }

    /**
     * Verify OTP for password reset
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!$record) {
            return response()->json(['message' => 'No OTP request found for this email.'], 404);
        }

        // Check if OTP is expired (10 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 400);
        }

        // Verify OTP
        if (!Hash::check($request->otp, $record->token)) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        return response()->json(['message' => 'OTP is valid.'], 200);
    }

    /**
     * Register animal owner (Admin/Staff only)
     */
    public function registerOwner(Request $request)
    {
        // Check if user has permission to register owners
        $user = auth()->user();
        \Log::info('User roles:', $user->roles->pluck('name')->toArray());
        if (!in_array($user->roles->pluck('name')->first(), ['admin', 'veterinarian', 'aew'])) {

            return response()->json([
                'message' => 'Unauthorized. Only admin, veterinarian, and AEW users can register animal owners.'
            ], 403);
        }

        // Get the flags first
        $hasNoEmail = $request->boolean('has_no_email');
        $hasNoPhone = $request->boolean('has_no_phone');
        
        // Build validation rules dynamically
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'role' => 'required|in:pet_owner,livestock_owner,poultry_owner',
            'barangay_id' => 'required|exists:barangays,id',
            'street' => 'required|string|max:255',
            'has_no_email' => 'boolean',
            'has_no_phone' => 'boolean',
        ];
        
        // Only validate email uniqueness if they actually have an email
        if (!$hasNoEmail) {
            $rules['email'] = 'required|email|unique:users,email';
        } else {
            $rules['email'] = 'nullable';
        }
        
        // Only validate phone uniqueness if they actually have a phone
        if (!$hasNoPhone) {
            $rules['phone_number'] = 'required|string|size:11|unique:users,phone_number';
        } else {
            $rules['phone_number'] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        // Generate unique placeholder values for users without email/phone
        $email = $hasNoEmail ? 'no-email-' . uniqid() . '@cityvet.local' : $validated['email'];
        $phoneNumber = $hasNoPhone ? '0000' . str_pad(rand(1, 9999999), 7, '0') : $validated['phone_number'];
        
        // Generate random password
        $password = Str::random(12);
        
        try {
            DB::beginTransaction();

            // Get role
            $role = Role::where('name', $validated['role'])->first();
            if (!$role) {
                return response()->json([
                    'message' => 'Invalid role specified.'
                ], 400);
            }

            // Create the user
            $newUser = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'birth_date' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'barangay_id' => $validated['barangay_id'],
                'street' => $validated['street'],
                'email' => $email,
                'phone_number' => $phoneNumber,
                'password' => Hash::make($password),
                'email_verified_at' => now(), // Auto-verify admin-created accounts
                'status' => 'active', // Auto-approve admin-created accounts
                'force_password_change' => true,
                'has_no_email' => $hasNoEmail,
                'has_no_phone' => $hasNoPhone
            ]);

            // Attach role to user using the many-to-many relationship
            $newUser->roles()->attach($role->id);

            // Send password via email only if they have a real email (not placeholder)
            if (!$hasNoEmail && filter_var($email, FILTER_VALIDATE_EMAIL) 
                && !str_contains($email, 'no-email') && !str_contains($email, '@cityvet.local')) {
                
                try {
                    Mail::send('emails.new_account_credentials', [
                        'user' => $newUser,
                        'password' => $password,
                        'created_by' => $user->first_name . ' ' . $user->last_name
                    ], function ($message) use ($email) {
                        $message->to($email)
                                ->subject('CityVet Account Created - Your Login Credentials');
                    });
                } catch (\Exception $e) {
                    // Log email error but don't fail the registration
                    \Log::error('Failed to send credentials email: ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Animal owner registered successfully!',
                'user' => [
                    'id' => $newUser->id,
                    'first_name' => $newUser->first_name,
                    'last_name' => $newUser->last_name,
                    'email' => $newUser->email,
                    'phone_number' => $newUser->phone_number,
                    'role' => $newUser->roles()->first()->name,
                    'has_no_email' => $hasNoEmail,
                    'has_no_phone' => $hasNoPhone,
                ],
                'credentials_sent' => !$hasNoEmail && 
                    filter_var($email, FILTER_VALIDATE_EMAIL) && 
                    !str_contains($email, 'no-email') && 
                    !str_contains($email, '@cityvet.local')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
