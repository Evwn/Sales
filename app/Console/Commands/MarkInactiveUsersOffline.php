<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Events\UserOnlineStatusChanged;

class MarkInactiveUsersOffline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:mark-offline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark users as offline if they haven\'t been active for more than 5 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inactiveThreshold = now()->subMinutes(5);
        
        $inactiveUsers = User::where('is_online', true)
            ->where('last_seen_at', '<', $inactiveThreshold)
            ->get();

        $count = 0;
        foreach ($inactiveUsers as $user) {
            $user->markAsOffline();
            event(new UserOnlineStatusChanged($user->id, false));
            $count++;
        }

        $this->info("Marked {$count} users as offline.");
    }
}
