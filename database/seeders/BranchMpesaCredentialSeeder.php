<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BranchMpesaCredential;

class BranchMpesaCredentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample M-PESA credentials for branch ID 1 (sandbox)
        BranchMpesaCredential::create([
            'branch_id' => 1,
            'environment' => 'sandbox',
            'is_active' => true,
            'is_testing' => true,
            'consumer_key' => 'j2HiswKnA3fPw8zZ2J7WWcos9tOxmfYYVvhCYPXOyK4I2MGI',
            'consumer_secret' => 'dmdMwdGCR75IL59PLnXlcGQXYK0GJ8f514MeadwjCOGoI4i3GjG9sGKCRMskRfJ0',
            'business_shortcode' => '174379',
            'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
            'description' => 'Sample sandbox credentials for testing'
        ]);

        // Sample M-PESA credentials for branch ID 1 (live) - for production
        BranchMpesaCredential::create([
            'branch_id' => 1,
            'environment' => 'live',
            'is_active' => false, // Set to false for safety
            'is_testing' => false,
            'consumer_key' => 'YOUR_LIVE_CONSUMER_KEY',
            'consumer_secret' => 'YOUR_LIVE_CONSUMER_SECRET',
            'business_shortcode' => 'YOUR_LIVE_SHORTCODE',
            'passkey' => 'YOUR_LIVE_PASSKEY',
            'description' => 'Live credentials for production - update with real values'
        ]);

        $this->command->info('Branch M-PESA credentials seeded successfully!');
        $this->command->info('Remember to update live credentials with real values before going to production.');
    }
} 