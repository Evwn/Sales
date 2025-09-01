<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Purchase extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'reference', 'supplier_id', 'quotation_id', 'location_id',
        'total_amount', 'discount', 'tax', 'status', 'payment_status',
        'order_date', 'expected_date', 'notes', 'additional_costs',
        'total_cost', 'created_by', 'updated_by'
    ];

        protected $casts = [
        'additional_costs' => 'array',
        'order_date' => 'date',
        'expected_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class, 'purchase_order_id');
    }

    public function invoices()
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockItems()
    {
        return $this->hasManyThrough(StockItem::class, PurchaseItem::class, 'purchase_id', 'id', 'id', 'stock_item_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_amount - $this->total_paid;
    }

    public function isFullyPaid(): bool
    {
        return $this->balance <= 0;
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public static function generateReference()
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');

    $lastReceipt = self::withTrashed()
                ->where('reference', 'like', "{$prefix}-{$date}-%")
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