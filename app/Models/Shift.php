<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeClockEntries(): HasMany
    {
        return $this->hasMany(TimeClockEntry::class);
    }

    public function shiftBalance(): HasMany
    {
        return $this->hasMany(ShiftBalance::class);
    }

    public function cashDrawerMovements(): HasMany
    {
        return $this->hasMany(CashDrawerMovement::class);
    }
}
