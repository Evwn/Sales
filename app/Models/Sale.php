<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\BarcodeService;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'customer_id',
        'seller_id',
        'business_id',
        'branch_id',
        'amount',
        'discount',
        'tax',
        'status',
        'payment_status',
        'payment_method',
        'sale_date',
        'barcode',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'sale_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->barcode)) {
                $sale->barcode = BarcodeService::generateSaleBarcode();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function items(): HasMany
    {
        return $this->saleItems();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'sale_items')
            ->withPivot(['quantity', 'unit_price', 'discount', 'tax'])
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class);
    }

    public function salesCommission(): HasMany
    {
        return $this->hasMany(SalesCommission::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get all sales receipts associated with the sale.
     */
    public function sales_receipts()
    {
        return $this->hasMany(\App\Models\SalesReceipt::class, 'sale_id');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->amount - $this->total_paid;
    }

    public function isFullyPaid(): bool
    {
        return $this->balance <= 0;
    }
} 