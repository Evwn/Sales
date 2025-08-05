<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTicketItem extends Model
{
    protected $fillable = [
        'ticket_id', 'item_id', 'variant_id', 'qty', 'price', 'subtotal','stock_item_id'
    ];

    public function ticket()
    {
        return $this->belongsTo(PosTicket::class, 'ticket_id');
    }
    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function variant()
    {
        return $this->belongsTo(ItemVariant::class, 'variant_id');
    }
} 