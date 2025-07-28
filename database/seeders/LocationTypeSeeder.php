<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('location_types')->insert([
            'name' => 'store',
            'created_at' => '2025-07-23 00:00:00',
            'updated_at' => '2025-07-23 00:00:00',
        ]);
    }
} 