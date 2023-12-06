<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RenewalPolicy
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
     */
    public function viewAll(User $user): Response
    {
        return Response::deny();
    }

    public function create(User $user): Response
    {
        return Response::deny();
    }

    /**
     * Can the current user view the user?
     */
    public function view(User $user, Squad $squad): Response
    {
        return Response::deny();
    }

    /**
     * Can the current user view the user?
     */
    public function update(User $user, Squad $squad): Response
    {
        return Response::deny();
    }

    public function delete(User $user, Squad $squad): Response
    {
        return Response::deny();
    }
}
