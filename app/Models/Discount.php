<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'amount', 'type', 'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isActive()
    {
        return now()->between($this->starts_at, $this->ends_at);
    }

    public function calculateDiscount($price)
    {
        if ($this->type === 'percentage') {
            return ($price * $this->value) / 100;
        }
        return $this->value;
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class);
    }
} 