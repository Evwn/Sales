<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
} 