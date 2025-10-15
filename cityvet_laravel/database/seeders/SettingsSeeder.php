<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'CityVet',
                'type' => 'string',
                'group' => 'general',
                'description' => 'The name of the application'
            ],
            [
                'key' => 'contact_email',
                'value' => 'cityvetofficial@gmail.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Main contact email address'
            ],
            [
                'key' => 'contact_phone',
                'value' => '',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Main contact phone number'
            ],
            [
                'key' => 'business_hours',
                'value' => 'Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 4:00 PM\nSunday: Closed',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Operating hours of the clinic'
            ],

            // Appearance Settings
            [
                'key' => 'app_theme',
                'value' => 'light',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Default theme for the application'
            ],

            // Notification Settings
            [
                'key' => 'notification_email',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable email notifications'
            ],
            [
                'key' => 'notification_new_appointments',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Notify when new appointments are created'
            ],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']], 
                $setting
            );
        }
    }
}
