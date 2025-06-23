<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SellerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->role->name === 'admin' || $user->ownedBusinesses()->exists() || $user->managedBusinesses()->exists();
    }

    public function view(User $user, User $seller): bool
    {
        if ($user->role->name === 'admin') {
            return true;
        }

        return $seller->sellerProfile->branch->business->owner_id === $user->id ||
            $seller->sellerProfile->branch->business->admins()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->role->name === 'admin' || $user->ownedBusinesses()->exists() || $user->managedBusinesses()->exists();
    }

    public function update(User $user, User $seller): bool
    {
        if ($user->role->name === 'admin') {
            return true;
        }

        return $seller->sellerProfile->branch->business->owner_id === $user->id ||
            $seller->sellerProfile->branch->business->admins()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, User $seller): bool
    {
        if ($user->role->name === 'admin') {
            return true;
        }

        return $seller->sellerProfile->branch->business->owner_id === $user->id ||
            $seller->sellerProfile->branch->business->admins()->where('user_id', $user->id)->exists();
    }
} 