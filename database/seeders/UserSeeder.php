<?php

namespace Database\Seeders;

use App\Mail\WelcomePasswordEmail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->createAdminUsers();

        // Create admin user
        User::updateOrCreate(
            ['email' => 'aldoyh@gmail.com'],
            [
                'name' => 'Hasan',
                'password' => Hash::make('97333334122'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'alsaryatv@gmail.com'],
            [
                'name' => 'AlSarya TEAM',
                'password' => Hash::make('97366632332'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'role' => 'admin',
            ]
        );

        // You can create more users here if needed
    }

    /**
     * Create admin users
     */
    private function createAdminUsers(): void
    {
        $usersList = [
            [
                'name' => 'Admin',
                'email' => 'aldoyh@gmail.com',
                'password' => bcrypt('97333334122'),
                'passplain' => '97333334122',
                'role' => 'admin',
            ],
            [
                'name' => 'Admin Bee',
                'email' => 'aldoyh@info.gov.bh',
                'password' => bcrypt('97333334122'),
                'passplain' => '97333334122',
                'role' => 'admin',
            ],
            [
                'name' => 'AlSarya Team',
                'email' => 'alsaryatv@gmail.com',
                'password' => bcrypt('97366632332'),
                'passplain' => '97366632332',
                'role' => 'admin',
            ],
        ];

        foreach ($usersList as $user) {
            try {
                // Check if user already exists
                if (User::where('email', $user['email'])->exists()) {
                    Log::info('User already exists: '.$user['email']);

                    continue;
                }

                $plainPass = $user['passplain'];
                User::create([
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'is_admin' => true,
                ]);
                Log::info('User created: '.$user['name']);

                // Send welcome email based on environment
                $this->sendWelcomeEmail($user, $plainPass);
            } catch (\Exception $e) {
                Log::error('Error creating user '.$user['email'].': '.$e->getMessage());
            }
        }
    }

    /**
     * Send welcome email based on environment
     *
     * @param  array  $user  User data
     * @param  string  $plainPass  Plain password
     */
    private function sendWelcomeEmail(array $user, string $plainPass): void
    {
        try {

            Mail::to($user['email'])->send(
                new WelcomePasswordEmail(
                    $plainPass,
                    $user['name'],
                    $user['email']
                )
            );
            Log::info('Email sent to: '.$user['email']);

            // Send to developer email in staging env
            Mail::to('aldoyh@gmail.com')->send(
                new WelcomePasswordEmail(
                    $plainPass,
                    $user['name'],
                    $user['email']
                )
            );
            Log::info('Staging email sent to developer');
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: '.$e->getMessage());
        }
    }
}
