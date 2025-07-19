<?php

namespace App\Http\Controllers\Web;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AdminAuthController extends Controller
{
    public function showLoginForm() {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();
            Auth::guard('admin')->login($admin);
            return redirect()->intended('/admin');
        }
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function showRegisterForm() {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.register');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required|confirmed|min:6',
        ]);
        $role_id = Role::where('name','admin')->first()->id;
        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $role_id,
            'password' => Hash::make($request->password),
        ]);
        
        return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
    }

    public function logout() {
        Auth::guard('admin')->logout();
        return redirect('/login');
    }

    public function showForgotPasswordForm() {
        return view('admin.forgot_password');
    }

    public function sendResetLink(Request $request) {
        $request->validate(['email' => 'required|email|exists:admins,email']);
        $token = Str::random(60);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );
        $resetLink = url('/admin/reset-password/' . $token . '?email=' . urlencode($request->email));
        Mail::raw("Reset your admin password: $resetLink", function ($message) use ($request) {
            $message->to($request->email)->subject('Admin Password Reset');
        });
        return back()->with('status', 'Password reset link sent to your email.');
    }

    public function showResetPasswordForm($token, Request $request) {
        $email = $request->query('email');
        return view('admin.reset_password', compact('token', 'email'));
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Invalid or expired token.']);
        }
        $admin = Admin::where('email', $request->email)->first();
        $admin->password = Hash::make($request->password);
        $admin->save();
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return redirect()->route('showLogin')->with('status', 'Password reset successful. You can now login.');
    }
} 