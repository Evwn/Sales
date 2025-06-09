<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'owner@example.com')->first();

        // Create demo businesses
        $businesses = [
            [
                'name' => 'Electronics Store',
                'description' => 'Selling electronics and gadgets',
                'owner_id' => $owner->id,
            ],
            [
                'name' => 'Fashion Boutique',
                'description' => 'Trendy fashion and accessories',
                'owner_id' => $owner->id,
            ],
        ];

        foreach ($businesses as $business) {
            Business::create($business);
        }
    }
}