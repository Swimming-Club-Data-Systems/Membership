<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SquadPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
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

    /**
     * Can the user view the list?
     *
     * @return void|bool
     */
    public function viewAll(User $user)
    {
        if ($user->hasPermission(['Galas', 'Committee'])) {
            return true;
        }
    }

    public function create(User $user)
    {

    }

    /**
     * Can the current user view the user?
     *
     * @return void|bool
     */
    public function view(User $user, Squad $squad)
    {
        if ($user->hasPermission(['Galas', 'Committee'])) {
            return true;
        }
    }

    /**
     * Can the current user view the user?
     *
     * @return void|bool
     */
    public function update(User $user, Squad $squad)
    {
        //        if ($user->hasPermission(['Galas', 'Committee'])) {
        //            return true;
        //        }
    }

    public function delete(User $user, Squad $squad)
    {

    }
}
