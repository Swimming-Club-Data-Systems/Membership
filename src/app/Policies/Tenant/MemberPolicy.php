<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Member;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPolicy
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
        if ($user->hasPermission(['Galas', 'Committee', 'Coach'])) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->hasPermission(['Admin', 'Coach'])) {
            return true;
        }
    }

    /**
     * Can the current user view the member?
     *
     * @return void|bool
     */
    public function view(User $user, Member $member)
    {
        if ($user->hasPermission(['Galas', 'Committee', 'Coach'])) {
            return true;
        }

        if ($user->UserID === $member->UserID) {
            return true;
        }
    }

    /**
     * Can the current user view the member?
     *
     * @return void|bool
     */
    public function update(User $user, Member $member)
    {
        if ($user->hasPermission(['Coach'])) {
            return true;
        }

        if ($user->UserID === $member->UserID) {
            return true;
        }
    }
}
