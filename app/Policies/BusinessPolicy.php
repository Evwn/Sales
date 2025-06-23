<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function view(User $user, Business $business): bool
    {
        return $user->id === $business->owner_id ||
            $business->admins()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Business $business): bool
    {
        return $user->id === $business->owner_id ||
            $business->admins()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Business $business): bool
    {
        return $user->id === $business->owner_id;
    }
} 