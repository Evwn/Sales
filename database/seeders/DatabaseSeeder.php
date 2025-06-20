<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BusinessSeeder::class,
            BranchSeeder::class,
            InventoryItemSeeder::class,
            ProductSeeder::class,
            DefaultCustomerSeeder::class,
        ]);
    }
}
