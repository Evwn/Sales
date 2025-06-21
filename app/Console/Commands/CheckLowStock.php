<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LowStockNotificationService;
use App\Models\Business;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock {--business-id= : Check specific business only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock products and send notifications to business owners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting low stock check...');

        $lowStockService = new LowStockNotificationService();

        if ($businessId = $this->option('business-id')) {
            // Check specific business
            $business = Business::with('owner')->find($businessId);
            
            if (!$business) {
                $this->error("Business with ID {$businessId} not found.");
                return 1;
            }

            $this->info("Checking low stock for business: {$business->name}");
            $lowStockService->checkBusinessLowStock($business);
            
        } else {
            // Check all businesses
            $this->info('Checking low stock for all businesses...');
            $lowStockService->checkAndNotifyLowStock();
        }

        $this->info('✅ Low stock check completed!');
        return 0;
    }
} 