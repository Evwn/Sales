<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
            protected $fillable = [
	'stock_transfer_id',
	'stock_item_id',
	'quantity',
    ];
        public function transfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }
    public function product()
{
    return $this->belongsTo(StockItem::class, 'stock_item_id'); 
}
// StockTransferItem.php
public function stockItem()
{
    return $this->belongsTo(StockItem::class, 'stock_item_id');
}


}
