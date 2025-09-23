<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\Setting;

class SettingsController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = [
            'theme' => Setting::get('app_theme', 'light'),
            'app_name' => Setting::get('app_name', 'CityVet'),
            'contact_email' => Setting::get('contact_email', ''),
            'contact_phone' => Setting::get('contact_phone', ''),
            'business_hours' => Setting::get('business_hours', ''),
            'notification_email' => Setting::get('notification_email', true),
            'notification_new_appointments' => Setting::get('notification_new_appointments', true),
        ];
        
        return view('admin.settings', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('status', 'Password updated successfully!');
    }

    /**
     * Update the admin's profile information.
     */
    public function updateProfile(Request $request)
    {
        $request->validateWithBag('updateProfile', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,' . Auth::id()],
        ]);

        $admin = Auth::user();
        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('profile_status', 'Profile updated successfully!');
    }

    /**
     * Update the admin's theme preference.
     */
    public function updateTheme(Request $request)
    {
        $request->validateWithBag('updateTheme', [
            'theme' => ['required', 'in:light,dark'],
        ]);

        Setting::set('app_theme', $request->theme, 'string', 'appearance', 'Application theme preference');

        return back()->with('theme_status', 'Theme updated successfully!');
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validateWithBag('updateSettings', [
            'app_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'business_hours' => ['nullable', 'string', 'max:500'],
            'notification_email' => ['boolean'],
            'notification_new_appointments' => ['boolean'],
        ]);

        Setting::set('app_name', $request->app_name, 'string', 'general', 'Application name');
        Setting::set('contact_email', $request->contact_email, 'string', 'general', 'Contact email address');
        Setting::set('contact_phone', $request->contact_phone, 'string', 'general', 'Contact phone number');
        Setting::set('business_hours', $request->business_hours, 'string', 'general', 'Business hours');
        Setting::set('notification_email', $request->has('notification_email'), 'boolean', 'notifications', 'Enable email notifications');
        Setting::set('notification_new_appointments', $request->has('notification_new_appointments'), 'boolean', 'notifications', 'Notify on new appointments');

        return back()->with('settings_status', 'Settings updated successfully!');
    }
}
