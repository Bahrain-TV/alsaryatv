<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        $defaultPassword = env('ADMIN_DEFAULT_PASSWORD', Str::random(32));

        $admins = [
            [
                'name' => 'Hasan',
                'email' => env('ADMIN_EMAIL_1', 'admin@alsarya.tv'),
                'password' => $defaultPassword,
                'role' => 'admin',
            ],
            [
                'name' => 'Admin Bee',
                'email' => env('ADMIN_EMAIL_2', 'admin2@alsarya.tv'),
                'password' => $defaultPassword,
                'role' => 'admin',
            ],
            [
                'name' => 'Super Admin',
                'email' => env('ADMIN_EMAIL_3', 'superadmin@alsarya.tv'),
                'password' => $defaultPassword,
                'role' => 'super_admin',
            ],
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
                    'role' => $admin['role'] ?? 'admin',
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
