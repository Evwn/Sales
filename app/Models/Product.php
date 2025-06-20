<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category_id',
        'unit_id',
        'purchase_price',
        'min_stock',
        'max_stock',
        'current_stock',
        'status',
        'branch_id',
        'inventory_item_id',
        'price',
        'buying_price',
        'stock',
        'min_stock_level'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'price' => 'decimal:2',
        'buying_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock_level' => 'integer'
    ];

    protected $with = ['inventoryItem']; // Always load the inventory item

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'sale_items')
            ->withPivot(['quantity', 'unit_price', 'discount', 'tax'])
            ->withTimestamps();
    }

    public function purchases(): BelongsToMany
    {
        return $this->belongsToMany(Purchase::class, 'purchase_items')
            ->withPivot(['quantity', 'unit_price', 'discount', 'tax'])
            ->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function manufacturingItems(): HasMany
    {
        return $this->hasMany(ManufacturingItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }

    public function isOverstocked(): bool
    {
        return $this->current_stock >= $this->max_stock;
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

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockValuation()
    {
        return $this->hasOne(StockValuation::class);
    }

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
}