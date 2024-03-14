<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;

class SquadMovePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if ($user->hasPermission('Admin')) {
            return true;
        }
    }

    public function viewAll(User $user)
    {
        return $user->hasPermission(['Coach']);
    }

    public function create(User $user)
    {
        return $user->hasPermission(['Coach']);
    }

    public function update(User $user)
    {
        return $user->hasPermission(['Coach']);
    }

    public function delete(User $user)
    {
        return $user->hasPermission(['Coach']);
    }
}
