<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemComponent extends Model
{
    protected $fillable = [
        'item_id', 'component_item_id', 'component_variant_id', 'quantity', 'cost'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function componentItem()
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }
    public function componentVariant()
    {
        return $this->belongsTo(ItemVariant::class, 'component_variant_id');
    }
    public function stockItem($locationId)
    {
        return StockItem::where('location_id', $locationId)
            ->where('item_id', $this->component_item_id)
            ->where(function ($query) {
                if ($this->component_variant_id) {
                    $query->where('variant_id', $this->component_variant_id);
                } else {
                    $query->whereNull('variant_id');
                }
            })->first();
    }
} 