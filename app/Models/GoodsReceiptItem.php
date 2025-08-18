<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
     protected $fillable = [
        'goods_receipt_id', 'purchase_order_item_id', 'item_id',
        'received_quantity', 'accepted_quantity', 'rejected_quantity', 'remarks'
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_order_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
