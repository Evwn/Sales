<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\BarcodeService;

class SalesReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'barcode',
        'sale_id',
        'business_id',
        'branch_id',
        'customer_id',
        'cashier_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'total_quantity',
        'loyalty_points_earned',
        'payment_methods',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'total_quantity' => 'decimal:2',
        'loyalty_points_earned' => 'decimal:2',
        'payment_methods' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (empty($receipt->barcode)) {
                $receipt->barcode = BarcodeService::generateReceiptBarcode();
            }
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReceiptItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function generateReceipt()
    {
        $business = $this->business;
        $branch = $this->branch;
        $customer = $this->customer;
        $cashier = $this->cashier;

        return [
            'receipt_number' => $this->reference,
            'business' => [
                'name' => $business->name,
                'address' => $business->address,
                'phone' => $business->phone,
                'email' => $business->email,
            ],
            'branch' => [
                'name' => $branch->name,
                'address' => $branch->address,
                'phone' => $branch->phone,
            ],
            'customer' => $customer ? [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'loyalty_points' => $customer->loyalty_points,
            ] : null,
            'cashier' => [
                'name' => $cashier->name,
                'id' => $cashier->id,
            ],
            'date' => $this->created_at->format('Y-m-d H:i:s'),
            'items' => $this->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'barcode' => $item->product_barcode,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'discount' => $item->discount,
                    'tax' => $item->tax,
                    'total' => $item->total,
                ];
            }),
            'summary' => [
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'tax' => $this->tax,
                'total' => $this->total,
                'total_quantity' => $this->total_quantity,
                'loyalty_points_earned' => $this->loyalty_points_earned,
            ],
            'payments' => collect($this->payment_methods)->map(function ($amount, $method) {
                return [
                    'method' => $method,
                    'amount' => $amount,
                ];
            }),
            'notes' => $this->notes,
        ];
    }

    public static function generateReference()
    {
        $prefix = 'REC';
        $date = now()->format('Ymd');
        $lastReceipt = self::where('reference', 'like', "{$prefix}{$date}%")
            ->orderBy('reference', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt->reference, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}{$date}{$newNumber}";
    }
} 