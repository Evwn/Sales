<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'balance',
        'currency',
        'status',
        'business_id',
        'branch_id',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }

    public function mpesaTransactions(): HasMany
    {
        return $this->hasMany(MpesaTransaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canWithdraw(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function deposit(float $amount): void
    {
        $this->balance += $amount;
        $this->save();
    }

    public function withdraw(float $amount): void
    {
        if ($this->canWithdraw($amount)) {
            $this->balance -= $amount;
            $this->save();
        }
    }
} 