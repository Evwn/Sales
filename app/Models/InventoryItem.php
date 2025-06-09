<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'brand',
        'model',
        'sku',
        'barcode',
        'upc',
        'ean',
        'isbn',
        'mpn',
        'unit',
        'unit_value',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'unit_value' => 'decimal:2',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    // Helper method to get formatted unit display
    public function getUnitDisplayAttribute(): string
    {
        if ($this->unit_value && $this->unit) {
            return "{$this->unit_value} {$this->unit}";
        }
        return $this->unit ?? '';
    }

    // Helper method to get full product name with brand
    public function getFullNameAttribute(): string
    {
        $parts = [];
        
        if ($this->brand) {
            $parts[] = $this->brand;
        }
        
        $parts[] = $this->name;
        
        if ($this->unit_value && $this->unit) {
            $parts[] = "({$this->unit_value} {$this->unit})";
        }
        
        return implode(' ', $parts);
    }
} 