<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftBalance extends Model
{
    protected $fillable = [
        'shift_id',
        'opening_balance',
        'closing_balance',
        'expected_close_cash',
        'real_close_cash',
        'opening_note',
        'closing_note',
        'closing_reason',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_close_cash' => 'decimal:2',
        'real_close_cash' => 'decimal:2',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
