<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Activity;

class UpdateActivityStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-activity-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update activity status to on going.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i');

        // Update activities that should be "on_going"
        $ongoingUpdated = Activity::where('status', 'up_coming')
            ->where('date', '<=', $today)
            ->where(function($query) use ($today, $currentTime) {
                $query->where('date', '<', $today)
                    ->orWhere(function($q) use ($today, $currentTime) {
                        $q->where('date', '=', $today)
                            ->where('time', '<=', $currentTime);
                    });
            })
            ->update(['status' => 'on_going']);

        $this->info("Activity status updated:");
        $this->info("- {$ongoingUpdated} activities set to 'on_going'");

        return 0;
    }
}
