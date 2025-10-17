<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration populates animal_type_id and animal_breed_id based on existing type and breed strings
     */
    public function up(): void
    {
        // Get all animals
        $animals = DB::table('animals')->whereNull('animal_type_id')->get();

        foreach ($animals as $animal) {
            // Find matching animal type
            $animalType = DB::table('animal_types')
                ->where('name', strtolower($animal->type))
                ->first();

            if ($animalType) {
                $animalTypeId = $animalType->id;
                $animalBreedId = null;

                // Find matching breed
                if ($animal->breed) {
                    $breed = DB::table('animal_breeds')
                        ->where('animal_type_id', $animalTypeId)
                        ->where('name', $animal->breed)
                        ->first();

                    if ($breed) {
                        $animalBreedId = $breed->id;
                    } else {
                        // If breed doesn't exist, look for "No Breed" option
                        $noBreed = DB::table('animal_breeds')
                            ->where('animal_type_id', $animalTypeId)
                            ->where('name', 'No Breed')
                            ->first();
                        
                        if ($noBreed) {
                            $animalBreedId = $noBreed->id;
                        }
                    }
                }

                // Update animal with foreign keys
                DB::table('animals')
                    ->where('id', $animal->id)
                    ->update([
                        'animal_type_id' => $animalTypeId,
                        'animal_breed_id' => $animalBreedId,
                    ]);
            }
        }

        echo "Animal types and breeds migrated successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset foreign keys to null
        DB::table('animals')->update([
            'animal_type_id' => null,
            'animal_breed_id' => null,
        ]);
    }
};
