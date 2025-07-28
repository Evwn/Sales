<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Store;

class StorePolicy
{
    public function view(User $user, Store $store)
    {
        return $user->ownedBusinesses()->pluck('id')->contains($store->business_id);
    }
    public function update(User $user, Store $store)
    {
        return $this->view($user, $store);
    }
    public function delete(User $user, Store $store)
    {
        return $this->view($user, $store);
    }
} 