<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnimalTypesAndBreedsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define animal types with their breeds
        $animalData = [
            // Pets
            [
                'name' => 'dog',
                'display_name' => 'Dog',
                'category' => 'pet',
                'icon' => 'pets',
                'description' => 'Domesticated canines',
                'sort_order' => 1,
                'breeds' => [
                    'No Breed',
                    'Aspin',
                    'Shih Tzu',
                    'Golden Retriever',
                    'Labrador',
                    'German Shepherd',
                    'Poodle',
                    'Bulldog',
                    'Beagle',
                    'Mixed-Breed'
                ]
            ],
            [
                'name' => 'cat',
                'display_name' => 'Cat',
                'category' => 'pet',
                'icon' => 'pets',
                'description' => 'Domesticated felines',
                'sort_order' => 2,
                'breeds' => [
                    'No Breed',
                    'Puspin',
                    'Persian',
                    'Siamese',
                    'Maine Coon',
                    'British Shorthair',
                    'Ragdoll',
                    'Russian Blue',
                    'Mixed-Breed'
                ]
            ],
            
            // Livestock
            [
                'name' => 'cattle',
                'display_name' => 'Cattle',
                'category' => 'livestock',
                'icon' => 'agriculture',
                'description' => 'Domesticated bovines',
                'sort_order' => 3,
                'breeds' => [
                    'No Breed',
                    'Holstein',
                    'Brahman',
                    'Simmental',
                    'Native',
                    'Jersey',
                    'Angus'
                ]
            ],
            [
                'name' => 'goat',
                'display_name' => 'Goat',
                'category' => 'livestock',
                'icon' => 'agriculture',
                'description' => 'Domesticated caprines',
                'sort_order' => 4,
                'breeds' => [
                    'No Breed',
                    'Boer',
                    'Anglo-Nubian',
                    'Native',
                    'Saanen',
                    'Toggenburg'
                ]
            ],    
            // Poultry
            [
                'name' => 'chicken',
                'display_name' => 'Chicken',
                'category' => 'poultry',
                'icon' => 'egg',
                'description' => 'Domesticated fowl',
                'sort_order' => 5,
                'breeds' => [
                    'No Breed',
                    'Native',
                    'Rhode Island Red',
                    'Leghorn',
                    'Broiler',
                    'Layer',
                    'Bantam'
                ]
            ],
        ];

        foreach ($animalData as $data) {
            // Insert animal type
            $typeId = DB::table('animal_types')->insertGetId([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'category' => $data['category'],
                'icon' => $data['icon'],
                'description' => $data['description'],
                'is_active' => true,
                'sort_order' => $data['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert breeds for this animal type
            foreach ($data['breeds'] as $index => $breed) {
                DB::table('animal_breeds')->insert([
                    'animal_type_id' => $typeId,
                    'name' => $breed,
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (method_exists($this, 'command') && $this->command) {
            $this->command->info('Animal types and breeds seeded successfully!');
        }
    }
}
