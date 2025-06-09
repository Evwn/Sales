<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'business_id',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ownedBusinesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function managedBusinesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_admin')
            ->withTimestamps();
    }

    public function managedBranches()
    {
        if ($this->isSuperAdmin()) {
            return Branch::query();
        }

        if ($this->isBusinessAdmin()) {
            return Branch::where('business_id', $this->business_id);
        }

        if ($this->isSeller()) {
            return Branch::where('id', $this->branch_id);
        }

        return Branch::whereIn('business_id', $this->ownedBusinesses()->pluck('id'));
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin' && is_null($this->business_id);
    }

    public function isBusinessAdmin(): bool
    {
        return $this->role === 'admin' && !is_null($this->business_id);
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    public function canAccessBusiness($businessId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isBusinessAdmin()) {
            return $this->managedBusinesses()->where('id', $businessId)->exists();
        }

        if ($this->isSeller()) {
            return $this->business_id === $businessId;
        }

        return $this->ownedBusinesses()->where('id', $businessId)->exists();
    }

    public function canAccessBranch($branchId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isBusinessAdmin()) {
            return Branch::where('business_id', $this->business_id)
                ->where('id', $branchId)
                ->exists();
        }

        return $this->branch_id === $branchId;
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    public function businesses()
    {
        if ($this->isSuperAdmin()) {
            return Business::where(function ($query) {
                $query->where('owner_id', $this->id)
                    ->orWhereHas('admins', function ($q) {
                        $q->where('admin_id', $this->id);
                    });
            });
        }

        return Business::where(function ($query) {
            $query->where('owner_id', $this->id)
                ->orWhereHas('admins', function ($q) {
                    $q->where('admin_id', $this->id);
                })
                ->orWhere('id', $this->business_id);
        });
    }
}
