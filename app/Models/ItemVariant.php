<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVariant extends Model
{
    protected $fillable = [
        'item_id', 'options', 'price', 'cost', 'sku', 'barcode', 'image_path'
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function stockItems()
    {
        return $this->hasMany(StockItem::class, 'variant_id');
    }
    public function values()
    {
        return $this->hasMany(ItemVariantValue::class, 'item_variant_id');
    }
    public function variantValues()
    {
        return $this->hasManyThrough(VariantValue::class, ItemVariantValue::class, 'item_variant_id', 'id', 'id', 'variant_value_id');
    }

    public function getOptionsStringAttribute()
    {
        // Get all related VariantValue models
        $values = $this->variantValues()->with('option')->get();
        // Build array like ['32', 'blue']
        $parts = [];
        foreach ($values as $value) {
            $parts[] = $value->value;
        }
        return implode(' / ', $parts);
    }
} 