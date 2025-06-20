<?php

namespace App\Services;

use Illuminate\Support\Str;

class BarcodeService
{
    /**
     * Generate a unique barcode for sales
     * Format: SALE-{timestamp}-{random}
     */
    public static function generateSaleBarcode(): string
    {
        return 'SALE-' . time() . '-' . Str::random(6);
    }

    /**
     * Generate a unique barcode for sales receipts
     * Format: RECEIPT-{timestamp}-{random}
     */
    public static function generateReceiptBarcode(): string
    {
        return 'RECEIPT-' . time() . '-' . Str::random(6);
    }

    /**
     * Generate a unique barcode for sales receipt items
     * Format: ITEM-{timestamp}-{random}
     */
    public static function generateReceiptItemBarcode(): string
    {
        return 'ITEM-' . time() . '-' . Str::random(6);
    }
} 