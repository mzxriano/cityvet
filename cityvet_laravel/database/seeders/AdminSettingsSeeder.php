<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class AdminSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // System Settings
            [
                'key' => 'system_name',
                'value' => 'CityVet Admin',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Application name displayed in the admin panel'
            ],
            [
                'key' => 'contact_email',
                'value' => 'admin@cityvet.com',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Main contact email for the system'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1234567890',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Main contact phone number'
            ],
            [
                'key' => 'business_hours',
                'value' => 'Monday - Friday: 8:00 AM - 5:00 PM',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Business operating hours'
            ],
            
            // Activity Settings
            [
                'key' => 'default_activity_duration',
                'value' => '60',
                'type' => 'integer',
                'group' => 'activities',
                'description' => 'Default duration for activities in minutes'
            ],
            [
                'key' => 'max_advance_booking_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'activities',
                'description' => 'Maximum days in advance for booking activities'
            ],
            [
                'key' => 'auto_approve_activities',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'activities',
                'description' => 'Automatically approve new activity submissions'
            ],
            
            // Notification Settings
            [
                'key' => 'email_notifications_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable email notifications for admins'
            ],
            [
                'key' => 'notify_new_activities',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Send notifications when new activities are created'
            ],
            [
                'key' => 'notify_activity_updates',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Send notifications when activities are updated'
            ],
            [
                'key' => 'notify_community_reports',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Send notifications for new community reports'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
