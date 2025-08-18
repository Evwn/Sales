<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

        protected $fillable = [
        'purchase_id', 'stock_item_id', 'unit_price', 'discount', 'tax',
        'item_id', 'variant_id', 'quantity_ordered', 'quantity_received',
        'purchase_cost', 'proportional_additional_cost', 'status'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }

    public function variant()
    {
        return $this->belongsTo(\App\Models\ItemVariant::class, 'variant_id');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal - $this->discount + $this->tax;
    }
        public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class, 'purchase_order_item_id');
    }
} 