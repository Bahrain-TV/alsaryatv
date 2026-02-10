<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAdminUsers();
    }

    /**
     * Seed admin users
     */
    private function seedAdminUsers(): void
    {
        $admins = [
            [
                'name' => 'Hasan',
                'email' => 'aldoyh@gmail.com',
                'password' => '97333334122',
            ],
            [
                'name' => 'Admin Bee',
                'email' => 'aldoyh@info.gov.bh',
                'password' => '97333334122',
            ],
            // [
            //     'name' => 'AlSarya TEAM',
            //     'email' => 'alsaryatv@gmail.com',
            //     'password' => '97366632332',
            // ],
        ];

        foreach ($admins as $admin) {
            $user = User::where('email', $admin['email'])->first();
            $isNew = ! $user;

            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                    'role' => 'admin',
                ]
            );

            Log::info(($isNew ? 'Created' : 'Updated')." admin user: {$admin['email']}");

            // Send welcome email ONLY in production and ONLY for new users
            if (app()->environment('production') && $isNew) {
                $this->command->call('app:send-welcome-email', [
                    'email' => $admin['email'],
                    'name' => $admin['name'],
                    'password' => $admin['password'],
                ]);
            }
        }
    }
}
