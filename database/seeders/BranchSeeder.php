<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Business;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $businesses = Business::all();

        foreach ($businesses as $business) {
            // Create main branch
            Branch::create([
                'name' => 'Main Branch',
                'address' => '123 Main Street',
                'phone' => '123-456-7890',
                'business_id' => $business->id,
            ]);

            // Create secondary branch
            Branch::create([
                'name' => 'Branch 2',
                'address' => '456 Second Street',
                'phone' => '123-456-7891',
                'business_id' => $business->id,
            ]);
        }
    }
} 