<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Mail\LowStockNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class LowStockNotificationService
{
    /**
     * Check for low stock products and send notifications
     */
    public function checkAndNotifyLowStock()
    {
        Log::info('LowStockNotificationService: Starting low stock check');

        // Get all businesses
        $businesses = Business::with('owner')->get();

        foreach ($businesses as $business) {
            $this->checkBusinessLowStock($business);
        }

        Log::info('LowStockNotificationService: Completed low stock check for all businesses');
    }

    /**
     * Check low stock for a specific business
     */
    public function checkBusinessLowStock(Business $business)
    {
        Log::info('LowStockNotificationService: Checking low stock for business', [
            'business_id' => $business->id,
            'business_name' => $business->name
        ]);

        // Get products for this business that are low on stock
        $lowStockProducts = Product::where('business_id', $business->id)
            ->where(function ($query) {
                $query->whereRaw('stock <= min_stock_level')
                      ->orWhere('stock', '<=', 5); // Also notify if stock is 5 or less
            })
            ->where('status', 1) // Only active products
            ->get();

        if ($lowStockProducts->count() > 0) {
            Log::info('LowStockNotificationService: Found low stock products', [
                'business_id' => $business->id,
                'low_stock_count' => $lowStockProducts->count(),
                'products' => $lowStockProducts->pluck('name')->toArray()
            ]);

            $this->sendLowStockNotification($business, $lowStockProducts);
        } else {
            Log::info('LowStockNotificationService: No low stock products found for business', [
                'business_id' => $business->id
            ]);
        }
    }

    /**
     * Send low stock notification email
     */
    public function sendLowStockNotification(Business $business, $lowStockProducts)
    {
        try {
            $businessOwner = $business->owner;

            if (!$businessOwner || !$businessOwner->email) {
                Log::error('LowStockNotificationService: Business owner not found or no email', [
                    'business_id' => $business->id,
                    'owner_id' => $business->owner_id,
                    'owner_email' => $businessOwner->email ?? 'null'
                ]);
                return;
            }

            Log::info('LowStockNotificationService: Sending low stock notification', [
                'business_id' => $business->id,
                'business_name' => $business->name,
                'owner_email' => $businessOwner->email,
                'low_stock_count' => $lowStockProducts->count()
            ]);

            // Send the email
            Mail::to($businessOwner->email)
                ->send(new LowStockNotificationMail($business, $lowStockProducts, $businessOwner));

            Log::info('LowStockNotificationService: Low stock notification sent successfully', [
                'business_id' => $business->id,
                'owner_email' => $businessOwner->email
            ]);

        } catch (\Exception $e) {
            Log::error('LowStockNotificationService: Failed to send low stock notification', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if a specific product is low on stock
     */
    public function isProductLowStock(Product $product): bool
    {
        return $product->stock <= $product->min_stock_level || $product->stock <= 5;
    }

    /**
     * Get low stock products for a business
     */
    public function getLowStockProducts(Business $business)
    {
        return Product::where('business_id', $business->id)
            ->where(function ($query) {
                $query->whereRaw('stock <= min_stock_level')
                      ->orWhere('stock', '<=', 5);
            })
            ->where('status', 1)
            ->get();
    }
} 