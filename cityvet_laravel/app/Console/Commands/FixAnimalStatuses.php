<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Animal;
use App\Models\AnimalArchive;

class FixAnimalStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animals:fix-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix animal statuses based on their archive records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing animal statuses...');
        
        $archives = AnimalArchive::all();
        $updated = 0;
        
        foreach ($archives as $archive) {
            $animal = Animal::find($archive->animal_id);
            
            if ($animal && $animal->status === 'alive') {
                $newStatus = $archive->archive_type === 'deceased' ? 'deceased' : 'deleted';
                
                $animal->update(['status' => $newStatus]);
                
                $this->info("Updated animal {$animal->id} ({$animal->name}) status to {$newStatus}");
                $updated++;
            }
        }
        
        $this->info("Fixed {$updated} animal statuses.");
    }
}
