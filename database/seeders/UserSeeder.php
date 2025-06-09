<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Business;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create demo business owner
        User::create([
            'name' => 'Business Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        // Create demo seller
        User::create([
            'name' => 'Demo Seller',
            'email' => 'seller@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'email_verified_at' => now(),
        ]);

        // Create Super Admin User
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create a business
        $business1 = Business::create([
            'name' => 'Business One',
            'description' => 'First sample business',
            'owner_id' => $superAdmin->id,
        ]);

        // Create branches for first business
        $branch1 = Branch::create([
            'name' => 'Main Branch',
            'address' => '123 Main St',
            'phone' => '123-456-7890',
            'business_id' => $business1->id,
        ]);

        $branch2 = Branch::create([
            'name' => 'Downtown Branch',
            'address' => '456 Downtown Ave',
            'phone' => '123-456-7891',
            'business_id' => $business1->id,
        ]);

        // Create Business Admin
        $businessAdmin = User::create([
            'name' => 'Business Admin',
            'email' => 'businessadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'business_id' => $business1->id,
        ]);

        // Create Seller for Main Branch
        $seller1 = User::create([
            'name' => 'Main Branch Seller',
            'email' => 'seller1@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'business_id' => $business1->id,
            'branch_id' => $branch1->id,
        ]);

        // Create Seller for Downtown Branch
        $seller2 = User::create([
            'name' => 'Downtown Branch Seller',
            'email' => 'seller2@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'business_id' => $business1->id,
            'branch_id' => $branch2->id,
        ]);

        // Create a second business
        $business2 = Business::create([
            'name' => 'Business Two',
            'description' => 'Second sample business',
            'owner_id' => $superAdmin->id,
        ]);

        // Create branch for second business
        $branch3 = Branch::create([
            'name' => 'Business Two Branch',
            'address' => '789 Second St',
            'phone' => '123-456-7892',
            'business_id' => $business2->id,
        ]);

        // Create Business Admin for second business
        $businessAdmin2 = User::create([
            'name' => 'Business Two Admin',
            'email' => 'businessadmin2@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'business_id' => $business2->id,
        ]);

        // Create Seller for second business
        $seller3 = User::create([
            'name' => 'Business Two Seller',
            'email' => 'seller3@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'business_id' => $business2->id,
            'branch_id' => $branch3->id,
        ]);
    }
} 