<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin user
        User::firstOrCreate(
            ['email' => 'superadmin@alsarya.tv'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'), // Change this on first login
                'role' => 'super_admin',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create regular admin user
        User::firstOrCreate(
            ['email' => 'admin@alsarya.tv'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create manager user
        User::firstOrCreate(
            ['email' => 'manager@alsarya.tv'],
            [
                'name' => 'Manager',
                'password' => bcrypt('password'),
                'role' => 'manager',
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}
