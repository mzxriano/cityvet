@extends('layouts.layout')

@section('content')
<div class="max-w-4xl mx-auto mt-10 space-y-8">
    <!-- System Settings -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">System Settings</h2>
        @if (session('settings_status'))
            <div class="mb-4 text-green-600 dark:text-green-400">{{ session('settings_status') }}</div>
        @endif
        @if ($errors->updateSettings->any())
            <div class="mb-4 text-red-600 dark:text-red-400">
                <ul>
                    @foreach ($errors->updateSettings->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('settings.system.update') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="app_name" class="block text-gray-700 dark:text-gray-300">Application Name</label>
                    <input id="app_name" type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="contact_email" class="block text-gray-700 dark:text-gray-300">Contact Email</label>
                    <input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="contact_phone" class="block text-gray-700 dark:text-gray-300">Contact Phone</label>
                    <input id="contact_phone" type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white">
                </div>
                {{-- <div>
                    <label for="business_hours" class="block text-gray-700 dark:text-gray-300">Business Hours</label>
                    <textarea id="business_hours" name="business_hours" rows="3" class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white">{{ old('business_hours', $settings['business_hours']) }}</textarea>
                </div> --}}
            </div>
            
            {{-- <div class="mt-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Notification Settings</h3>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="notification_email" value="1" {{ $settings['notification_email'] ? 'checked' : '' }} class="mr-2">
                        <span class="dark:text-gray-300">Enable email notifications</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="notification_new_appointments" value="1" {{ $settings['notification_new_appointments'] ? 'checked' : '' }} class="mr-2">
                        <span class="dark:text-gray-300">Notify on new appointments</span>
                    </label>
                </div>
            </div> --}}
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update System Settings</button>
            </div>
        </form>
    </div>

    <!-- Theme Settings -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">Appearance Settings</h2>
        @if (session('theme_status'))
            <div class="mb-4 text-green-600 dark:text-green-400">{{ session('theme_status') }}</div>
        @endif
        @if ($errors->updateTheme->any())
            <div class="mb-4 text-red-600 dark:text-red-400">
                <ul>
                    @foreach ($errors->updateTheme->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('settings.theme.update') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 mb-2">Choose Theme</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="theme" value="light" {{ $settings['theme'] === 'light' ? 'checked' : '' }} class="mr-2">
                        <span class="flex items-center dark:text-gray-300">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                            </svg>
                            Light Theme
                        </span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="theme" value="dark" {{ $settings['theme'] === 'dark' ? 'checked' : '' }} class="mr-2">
                        <span class="flex items-center dark:text-gray-300">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            Dark Theme
                        </span>
                    </label>
                </div>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Theme</button>
        </form>
    </div>

    <!-- Admin Profile Settings -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">Admin Profile</h2>
        @if (session('profile_status'))
            <div class="mb-4 text-green-600 dark:text-green-400">{{ session('profile_status') }}</div>
        @endif
        @if ($errors->updateProfile->any())
            <div class="mb-4 text-red-600 dark:text-red-400">
                <ul>
                    @foreach ($errors->updateProfile->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('settings.profile.update') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-gray-700 dark:text-gray-300">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="email" class="block text-gray-700 dark:text-gray-300">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Profile</button>
            </div>
        </form>
    </div>

    <!-- Password Change -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">Change Password</h2>
        @if (session('status'))
            <div class="mb-4 text-green-600 dark:text-green-400">{{ session('status') }}</div>
        @endif
        @if ($errors->updatePassword->any())
            <div class="mb-4 text-red-600 dark:text-red-400">
                <ul>
                    @foreach ($errors->updatePassword->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('settings.password.update') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="current_password" class="block text-gray-700 dark:text-gray-300">Current Password</label>
                    <input id="current_password" type="password" name="current_password" required class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white" autocomplete="current-password">
                </div>
                <div>
                    <label for="password" class="block text-gray-700 dark:text-gray-300">New Password</label>
                    <input id="password" type="password" name="password" required class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white" autocomplete="new-password">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-gray-700 dark:text-gray-300">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full border dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-700 dark:text-white" autocomplete="new-password">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
