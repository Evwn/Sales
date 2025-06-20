<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VatReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vat_returns';

    protected $fillable = [
        'period',
        'total_sales',
        'total_purchases',
        'vat_amount',
        'status',
        'tims_reference',
        'response_data',
    ];

    protected $casts = [
        'total_sales' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'response_data' => 'array',
    ];

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function getNetVatAttribute(): float
    {
        return $this->total_sales - $this->total_purchases;
    }

    public function getFormattedTotalSalesAttribute(): string
    {
        return number_format($this->total_sales, 2);
    }

    public function getFormattedTotalPurchasesAttribute(): string
    {
        return number_format($this->total_purchases, 2);
    }

    public function getFormattedVatAmountAttribute(): string
    {
        return number_format($this->vat_amount, 2);
    }
} 