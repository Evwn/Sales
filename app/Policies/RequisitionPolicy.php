<?php

namespace App\Policies;

use App\Models\Requisition;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RequisitionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Requisition $requisition): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Requisition $requisition): bool
    {
        // Only the creator can update
        return $user->id === $requisition->user_id;
    }

    /**
     * Can the user delete this requisition?
     */
    public function delete(User $user, Requisition $requisition): bool
    {
        return $user->id === $requisition->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Requisition $requisition): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Requisition $requisition): bool
    {
        return false;
    }
}
