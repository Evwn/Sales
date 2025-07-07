<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'address',
        'gps_latitude',
        'gps_longitude',
        'phone',
        'email',
        'barcode_path',
        'status',
    ];

    protected $casts = [
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(User::class)->whereHas('roles', function($q) {
            $q->where('name', 'seller');
        });
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->business->city}, {$this->business->country}";
    }

    public function getGpsLocationAttribute(): ?array
    {
        if (!$this->gps_latitude || !$this->gps_longitude) {
            return null;
        }

        return [
            'latitude' => $this->gps_latitude,
            'longitude' => $this->gps_longitude,
        ];
    }

    public function updateGpsLocation(float $latitude, float $longitude): bool
    {
        return $this->update([
            'gps_latitude' => $latitude,
            'gps_longitude' => $longitude,
        ]);
    }

    public function generateBarcode(): string
    {
        // Generate a unique barcode for the branch
        $barcode = 'BR' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
        $this->barcode_path = $barcode;
        $this->save();
        return $barcode;
    }
} 