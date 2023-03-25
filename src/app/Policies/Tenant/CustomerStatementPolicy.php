<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CustomerStatement;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CustomerStatementPolicy
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
        if ($user->hasPermission(['Admin'])) {
            return Response::allow();
        }
    }

    public function viewIndex(User $user): Response
    {
        return Response::denyAsNotFound();
    }

    public function view(User $user, CustomerStatement $statement): Response
    {
        if ($user->UserID == $statement->user->UserID) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }
}
