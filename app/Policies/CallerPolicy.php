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
}
