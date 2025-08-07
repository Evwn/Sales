<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    protected $fillable = [
        'location_id', 'item_id', 'variant_id', 'quantity', 'min_stock_level', 'max_stock_level', 'price','cost'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function variant()
    {
        return $this->belongsTo(ItemVariant::class, 'variant_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
} 