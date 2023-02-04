<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\LedgerAccount;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class LedgerAccountPolicy
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
        if ($user->hasPermission(['Admin', 'Coach', 'Galas'])) {
            return Response::allow();
        }
    }

    public function create(User $user)
    {

    }

    public function view(User $user, LedgerAccount $account)
    {

    }

    public function viewAll(User $user)
    {

    }
}
