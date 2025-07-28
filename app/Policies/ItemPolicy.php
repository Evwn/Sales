<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Item;

class ItemPolicy
{
    public function view(User $user, Item $item)
    {
        return (
            $user->id === $item->user_id ||
            ($user->business_id && $item->user && $user->business_id === $item->user->business_id) ||
            $user->hasPermissionTo('manage items')
        );
    }

    public function update(User $user, Item $item)
    {
        return (
            $user->id === $item->user_id ||
            ($user->business_id && $item->user && $user->business_id === $item->user->business_id) ||
            $user->hasPermissionTo('manage items')
        );
    }

    public function delete(User $user, Item $item)
    {
        return (
            $user->id === $item->user_id ||
            ($user->business_id && $item->user && $user->business_id === $item->user->business_id) ||
            $user->hasPermissionTo('manage items')
        );
    }
} 