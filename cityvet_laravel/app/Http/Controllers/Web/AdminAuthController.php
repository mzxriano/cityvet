<?php

namespace App\Http\Controllers\Web;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
} 