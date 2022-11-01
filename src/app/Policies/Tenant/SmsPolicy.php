<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SmsPolicy
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

    public function before(User $user)
    {
        if ($user->hasPermission('Admin')) {
            return Response::allow();
        }
    }

    public function create(User $user)
    {
        if ($user->hasPermission('Admin')) {
            return Response::allow();
        } else {
            return Response::denyAsNotFound();
        }
    }

    public function view(User $user)
    {
        if ($user->hasPermission(['Admin', 'Coach', 'Galas'])) {
            return Response::allow();
        } else {
            return Response::denyAsNotFound();
        }
    }
}
