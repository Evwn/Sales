<?php

namespace App\Console\Commands;

use App\Services\PesaPalService;
use Illuminate\Console\Command;

class TestPesaPalSTK extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pesapal:test-stk {phone} {amount=10} {description=Test Payment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PesaPal STK Push payment';

    /**
     * Execute the console command.
     */
    public function handle(PesaPalService $pesaPalService)
    {
        $phone = $this->argument('phone');
        $amount = $this->argument('amount');
        $description = $this->argument('description');

        $this->info("=== PesaPal STK Push Test ===");
        $this->info("Phone: $phone");
        $this->info("Amount: KES $amount");
        $this->info("Description: $description");
        $this->info("Environment: " . config('pesapal.environment'));
        $this->newLine();

        // Format phone number
        if (!str_starts_with($phone, '254')) {
            $phone = '254' . ltrim($phone, '0');
            $this->info("Formatted phone: $phone");
        }

        $this->info("1. Getting access token...");
        $token = $pesaPalService->getAccessToken();
        if (!$token) {
            $this->error("Failed to get access token");
            return 1;
        }
        $this->info("✅ Access token obtained");

        $this->info("2. Initiating STK Push...");
        $result = $pesaPalService->initiateSTKPush($phone, $amount, $description);

        if ($result) {
            $this->info("✅ STK Push initiated successfully");
            $this->info("Order Tracking ID: " . ($result['order_tracking_id'] ?? 'N/A'));
            $this->info("Merchant Reference: " . ($result['merchant_reference'] ?? 'N/A'));
            
            $this->newLine();
            $this->info("=== Next Steps ===");
            $this->info("1. Check your phone for the STK push notification");
            $this->info("2. Enter your PIN to complete the payment");
            $this->info("3. Run: php artisan pesapal:check-status " . ($result['order_tracking_id'] ?? 'TRACKING_ID'));
            
            return 0;
        } else {
            $this->error("❌ Failed to initiate STK Push");
            return 1;
        }
    }
} 