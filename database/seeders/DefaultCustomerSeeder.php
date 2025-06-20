<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class DefaultCustomerSeeder extends Seeder
{
    public function run()
    {
        Customer::create([
            'name' => 'Walk-in Customer',
            'email' => 'walkin@example.com',
            'phone' => '0000000000',
            'address' => 'Walk-in Customer',
            'credit_limit' => 0,
            'balance' => 0,
            'status' => true,
        ]);
    }
} 