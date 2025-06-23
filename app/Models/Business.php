<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'phone',
        'email',
        'tax_number',
        'registration_number',
        'industry',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'owner_id',
        'receipt_footer',
        'contact_information',
        'receipt_template',
    ];

    protected $appends = [
        'logo_url',
        'tax_document_url',
        'registration_document_url',
    ];

    protected $casts = [
        'receipt_template' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(User::class)->where('role_id', 3);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'business_admin')
            ->withTimestamps()
            ->whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function businessAdmins(): HasMany
    {
        return $this->hasMany(BusinessAdmin::class);
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    public function getTaxDocumentUrlAttribute()
    {
        return $this->tax_document_path ? Storage::url($this->tax_document_path) : null;
    }

    public function getRegistrationDocumentUrlAttribute()
    {
        return $this->registration_document_path ? Storage::url($this->registration_document_path) : null;
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
} 