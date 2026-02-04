<?php

namespace App\Console\Commands;

use App\Mail\WelcomePasswordEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-welcome-email {email} {name} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a welcome email to a new admin user with their credentials';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $password = $this->argument('password');

        try {
            Mail::to($email)->send(new WelcomePasswordEmail($password, $name, $email));
            
            // Also send a copy to the developer for confirmation in production/staging
            if (app()->environment('production')) {
                 Mail::to('aldoyh@gmail.com')->send(new WelcomePasswordEmail($password, $name, $email));
            }

            $this->info("Welcome email sent successfully to {$email}");
            Log::info("Welcome email sent to {$email}");
        } catch (\Exception $e) {
            $this->error("Failed to send welcome email to {$email}: " . $e->getMessage());
            Log::error("Failed to send welcome email to {$email}: " . $e->getMessage());
        }
    }
}
