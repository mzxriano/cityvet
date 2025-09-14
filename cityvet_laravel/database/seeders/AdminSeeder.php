<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'System Administrator']
        );

        Admin::updateOrCreate(
            ['email' => 'madgebite@gmail.com'], 
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
            ]
        );
    }
}
