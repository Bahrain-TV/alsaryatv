<?php

namespace App\Policies;

use App\Models\Caller;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CallerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * This allows anyone (including guests) to create callers
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(?User $user)
    {
        // Allow anyone to create callers
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Caller $caller)
    {
        // Only authenticated users can update callers
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     * Only Super Admins can perform hard deletes.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Caller $caller)
    {
        // Only super admins can delete
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Super Admins can perform force deletes.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Caller $caller)
    {
        // Only super admins can force delete
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     * Only Super Admins can restore deleted records.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Caller $caller)
    {
        // Only super admins can restore deleted records
        return $user->isSuperAdmin();
    }
}
