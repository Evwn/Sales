<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'business_id',
        'branch_id',
        'price',
        'buying_price',
        'stock',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'buying_price' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected $with = ['inventoryItem']; // Always load the inventory item

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class)
            ->withPivot(['quantity', 'price'])
            ->withTimestamps();
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'inventory')
            ->withPivot(['quantity', 'threshold']);
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    public function getActiveDiscount()
    {
        return $this->discounts()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }

    // Helper method to get the display name
    public function getDisplayNameAttribute(): string
    {
        return $this->inventoryItem ? $this->inventoryItem->full_name : $this->name;
    }

    // Helper method to get barcode
    public function getBarcodeAttribute($value): ?string
    {
        return $value ?? $this->inventoryItem?->barcode;
    }

    // Helper method to get SKU
    public function getSkuAttribute($value): ?string
    {
        return $value ?? $this->inventoryItem?->sku;
    }
}