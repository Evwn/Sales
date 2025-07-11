<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the admin role exists
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);

        // Create the admin user
        $user = User::firstOrCreate(
            ['email' => 'salesmanagement424@gmail.com'],
            [
                'name' => 'System Manager',
                'password' => Hash::make('S@les@2025'),
                'email_verified_at' => now(),
            ]
        );

        // Assign the admin role
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
} 