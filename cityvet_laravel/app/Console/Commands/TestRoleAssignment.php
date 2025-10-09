<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class TestRoleAssignment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:role-assignment {email} {animal_type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test automatic role assignment based on animal type';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $animalType = $this->argument('animal_type');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $this->info("User: {$user->first_name} {$user->last_name} ({$user->email})");
        $this->info("Current roles: " . $user->roles->pluck('name')->join(', '));

        // Test role assignment
        $this->assignRoleBasedOnAnimalType($user, $animalType);

        // Refresh user roles
        $user->refresh();
        $this->info("Roles after assignment: " . $user->roles->pluck('name')->join(', '));

        return 0;
    }

    /**
     * Automatically assign role to user based on animal type
     */
    private function assignRoleBasedOnAnimalType($user, $animalType)
    {
        // Define the mapping between animal types and roles
        $animalTypeToRole = [
            // Pets
            'dog' => 'pet_owner',
            'cat' => 'pet_owner',
            
            // Livestock
            'cattle' => 'livestock_owner',
            'goat' => 'livestock_owner',
            'carabao' => 'livestock_owner',
            
            // Poultry
            'chicken' => 'poultry_owner',
            'duck' => 'poultry_owner',
        ];

        $roleName = $animalTypeToRole[strtolower($animalType)] ?? null;
        
        if (!$roleName) {
            $this->warn("Unknown animal type for role assignment: {$animalType}");
            return;
        }

        // Check if user already has this role
        $existingRole = $user->roles()->where('name', $roleName)->first();
        if ($existingRole) {
            $this->info("User already has the '{$roleName}' role.");
            return;
        }

        // Find the role in the database
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role not found in database: {$roleName}");
            return;
        }

        // Assign the role to the user
        $user->roles()->attach($role->id);
        
        $this->info("Role '{$roleName}' automatically assigned to user based on animal type '{$animalType}'");
    }
}
