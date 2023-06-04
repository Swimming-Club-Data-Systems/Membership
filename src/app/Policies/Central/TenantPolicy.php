<?php

namespace App\Policies\Central;

use App\Models\Central\Tenant;
use App\Models\Central\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TenantPolicy
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
     * @return Response
     */
    public function before(User $user, $ability)
    {
        if ($user->id === 1) {
            return Response::allow();
        }
    }

    /**
     * Can the current user view the member?
     *
     * @return Response
     */
    public function manage(User $user, Tenant $tenant)
    {
        if ($user->tenants()->where('ID', $tenant->id)->exists()) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    /**
     * Can the current user update the tenant details page?
     *
     * @return Response
     */
    public function update(User $user, Tenant $tenant)
    {
        return Response::denyAsNotFound();
    }
}
