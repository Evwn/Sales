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
        // Get all businesses
        $businesses = Business::with('owner')->get();

        foreach ($businesses as $business) {
            $this->checkBusinessLowStock($business);
        }
    }

    /**
     * Check low stock for a specific business
     */
    public function checkBusinessLowStock(Business $business)
    {
        // Get products for this business that are low on stock
        $lowStockProducts = Product::where('business_id', $business->id)
            ->where(function ($query) {
                $query->whereRaw('stock <= min_stock_level')
                      ->orWhere('stock', '<=', 5); // Also notify if stock is 5 or less
            })
            ->where('status', 1) // Only active products
            ->get();

        if ($lowStockProducts->count() > 0) {
            $this->sendLowStockNotification($business, $lowStockProducts);
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
                return;
            }

            // Send the email
            Mail::to($businessOwner->email)
                ->send(new LowStockNotificationMail($business, $lowStockProducts, $businessOwner));

        } catch (\Exception $e) {
            // Handle error silently
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