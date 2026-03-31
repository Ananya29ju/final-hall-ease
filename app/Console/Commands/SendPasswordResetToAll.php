<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class SendPasswordResetToAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:send-reset-links {--role= : Limit to a specific role (e.g. user, admin, media)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a password reset link to all users or users of a specific role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = $this->option('role');
        $query = User::query();

        // ONLY target emails ending exactly with staloysius.edu.in
        $query->where('email', 'LIKE', '%@staloysius.edu.in');
        
        if ($role) {
            $query->where('role', $role);
            $this->info("Targeting only users with role: {$role} and @staloysius.edu.in emails");
        } else {
            $query->where('email', '!=', 'admin@example.com'); // Redundant but safe
        }

        $users = $query->get();
        $count = $users->count();

        if ($count === 0) {
            $this->warn('No users found.');
            return;
        }

        if (!$this->confirm("Are you sure you want to send password reset links to {$count} users?", true)) {
            $this->info('Operation cancelled.');
            return;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($users as $user) {
            try {
                // Generates token and automatically emails the user using their CustomResetPassword notification
                Password::sendResetLink(['email' => $user->email]);
            } catch (\Exception $e) {
                // In case of error, just continue to the next one
                $this->error("\nFailed to send to {$user->email}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nAll password reset links have been dispatched!");
    }
}
