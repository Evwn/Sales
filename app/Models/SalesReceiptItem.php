<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\BarcodeService;

class SalesReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_receipt_id',
        'stock_item_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount',
        'tax',
        'total',
        'barcode',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->barcode)) {
                $item->barcode = BarcodeService::generateReceiptItemBarcode();
            }
        });
    }

    public function salesReceipt(): BelongsTo
    {
        return $this->belongsTo(SalesReceipt::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
} 