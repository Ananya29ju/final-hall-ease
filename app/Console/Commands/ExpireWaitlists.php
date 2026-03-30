<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExpireWaitlists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waitlist:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire waitlists that have not been confirmed in time, and notify the next eligible user.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredWaitlists = \App\Models\Waitlist::where('status', 'notified')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredWaitlists as $waitlist) {
            $waitlist->update(['status' => 'expired']);

            // Find the next person in line for the same hall and date since this slot freed up again
            \App\Models\Waitlist::notifyNextInWaitlist($waitlist->hall_id, $waitlist->start_datetime, $waitlist->end_datetime);
            $count++;
        }

        $this->info("Expired $count waitlist(s) successfully.");
    }
}
