<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
      protected $fillable = [
        'reference', 'purchase_order_id', 'supplier_id', 'user_id',
        'receipt_date', 'status', 'remarks'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(Purchase::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

        public static function generateReference()
    {
        $prefix = 'GRN';
        $date = now()->format('Ymd');
        $lastReceipt = self::where('reference', 'like', "{$prefix}-{$date}-%")
            ->orderBy('reference', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt->reference, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$date}-{$newNumber}";
        }
}
