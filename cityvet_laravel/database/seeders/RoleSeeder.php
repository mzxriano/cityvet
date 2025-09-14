<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'System Administrator'],
            ['name' => 'pet_owner', 'description' => 'Pet owner who registers owned pets'],
            ['name' => 'livestock_owner', 'description' => 'Livestock owner who registers owned livestock'],
            ['name' => 'poultry_owner', 'description' => 'Poultry owner who registers owned poultry'],
            ['name' => 'staff', 'description' => 'Staff responsible for operations'],
            ['name' => 'veterinarian', 'description' => 'Licensed veterinarian who administers vaccines'],
            ['name' => 'aew', 'description' => 'Agricultural Extension Worker'],
            ['name' => 'sub_admin', 'description' => 'Sub-admin'],
            ['name' => 'barangay_personnel', 'description' => 'Barangay officer for reporting cases'],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                ['description' => $roleData['description']] 
            );
        }
    }
}
