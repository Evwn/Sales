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
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'owner_id', 'business_id', 'branch_id', 'location_id', 'supplier_id', 'status', 'order_date', 'expected_date', 'notes', 'additional_costs', 'total_cost', 'total_amount', 'discount', 'tax', 'payment_status', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'additional_costs' => 'array',
        'order_date' => 'date',
        'expected_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
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
        return 'PO-' . strtoupper(uniqid());
    }
} 