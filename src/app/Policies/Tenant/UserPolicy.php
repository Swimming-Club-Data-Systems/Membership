<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
     * @param User $user
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
     * @param User $user
     * @return void|bool
     */
    public function viewAll(User $user)
    {
        if ($user->hasPermission(['Galas', 'Committee'])) {
            return true;
        }
    }

    /**
     * Can the current user view the user?
     *
     * @param User $user
     * @param User $modelUser
     * @return void|bool
     */
    public function view(User $user, User $modelUser)
    {
        if ($user->hasPermission(['Galas', 'Committee'])) {
            return true;
        }
    }
}
