<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'business_id', 'branch_id', 'logo_path', 'logo_url', 'theme',
        'is_online', 'last_seen_at', 'typing_in_chat_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
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

    public function sales()
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    public function salesReceipts(): HasMany
    {
        return $this->hasMany(SalesReceipt::class, 'cashier_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'created_by');
    }

    public function managedBranches()
    {
        if ($this->hasRole('admin') && is_null($this->business_id)) {
            return Branch::query();
        }

        if ($this->hasRole('admin') && !is_null($this->business_id)) {
            return Branch::where('business_id', $this->business_id);
        }

        if ($this->hasRole('seller')) {
            return Branch::where('id', $this->branch_id);
        }

        return Branch::whereIn('business_id', $this->ownedBusinesses()->pluck('id'));
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('admin') && is_null($this->business_id);
    }

    public function isBusinessAdmin(): bool
    {
        return $this->hasRole('admin') && !is_null($this->business_id);
    }

    public function isSeller(): bool
    {
        return $this->hasRole('seller');
    }

    public function businesses()
    {
        if ($this->isSuperAdmin()) {
            return Business::where(function ($query) {
                $query->where('owner_id', $this->id)
                    ->orWhereHas('admins', function ($q) {
                        $q->where('user_id', $this->id);
                    });
            });
        }

        return Business::where(function ($query) {
            $query->where('owner_id', $this->id)
                ->orWhereHas('admins', function ($q) {
                    $q->where('user_id', $this->id);
                })
                ->orWhere('id', $this->business_id);
        });
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

    public function salesCommission()
    {
        return $this->hasMany(SalesCommission::class, 'seller_id');
    }

    // Chat-related relationships
    public function ownedChats()
    {
        return $this->hasMany(Chat::class, 'owner_id');
    }

    public function sellerChats()
    {
        return $this->hasMany(Chat::class, 'seller_id');
    }

    public function chatParticipants()
    {
        return $this->hasMany(ChatParticipant::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'user_id');
    }

    // Online status methods
    public function markAsOnline()
    {
        $this->update([
            'is_online' => true,
            'last_seen_at' => now()
        ]);
    }

    public function markAsOffline()
    {
        $this->update([
            'is_online' => false,
            'last_seen_at' => now()
        ]);
    }

    public function updateLastSeen()
    {
        $this->update(['last_seen_at' => now()]);
    }

    public function setTypingStatus($chatId)
    {
        $this->update(['typing_in_chat_id' => $chatId]);
    }

    public function clearTypingStatus()
    {
        $this->update(['typing_in_chat_id' => null]);
    }

    public function getOnlineStatus()
    {
        if ($this->is_online) {
            return 'online';
        }
        
        if ($this->last_seen_at) {
            $diff = now()->diffInMinutes($this->last_seen_at);
            if ($diff < 1) {
                return 'just now';
            } elseif ($diff < 60) {
                return $diff . ' minutes ago';
            } elseif ($diff < 1440) {
                return floor($diff / 60) . ' hours ago';
            } else {
                return $this->last_seen_at->format('M j');
            }
        }
        
        return 'offline';
    }
}
