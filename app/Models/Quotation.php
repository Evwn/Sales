<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quotation extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'requisition_id', 'reference', 'supplier_id',
        'user_id', 'total_amount', 'status', 'valid_until'
    ];

        protected $casts = [
        'total_amount' => 'decimal:2',
        'valid_until' => 'datetime',
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
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
        return $this->hasMany(QuotationItem::class);
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'quotation_items')
            ->withPivot(['quantity', 'unit_price', 'discount', 'tax'])
            ->withTimestamps();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
        public function approved(): bool
    {
        return $this->status === 'approved';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->valid_until->isPast();
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedValidUntilAttribute(): string
    {
        return $this->valid_until->format('Y-m-d H:i:s');
    }
        public function suppliers() {
        return $this->belongsToMany(Supplier::class, 'quotation_suppliers')
            ->withPivot('id','status')
            ->withTimestamps();
    }
public static function generateReference()
{
    $prefix = 'QUO';
    $date = now()->format('Ymd');

    return \DB::transaction(function () use ($prefix, $date) {
        for ($i = 0; $i < 5; $i++) {

            $lastReceipt = self::withTrashed()
                ->where('reference', 'like', "{$prefix}-{$date}-%")
                ->lockForUpdate()
                ->orderBy('reference', 'desc')
                ->first();

            if ($lastReceipt) {
                $lastNumber = (int) substr($lastReceipt->reference, -4);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            $ref = "{$prefix}-{$date}-{$newNumber}";
            \Log::info($ref);

            if (! self::where('reference', $ref)->exists()) {
                return $ref;
            }

            usleep(50000); // wait before retry
        }

        throw new \Exception('Unable to generate unique reference after retries.');
    });
}

} 