<?php

namespace App\Policies\Central;

use App\Models\Central\Tenant;
use App\Models\Central\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
     * @param User $user
     * @param string $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if ($user->id === 1) {
            return true;
        }
    }

    /**
     * Can the current user view the member?
     *
     * @param User $user
     * @param Tenant $tenant
     * @return void|bool
     */
    public function manage(User $user, Tenant $tenant)
    {
        if ($user->tenants()->where('ID', $tenant->id)->exists()) {
            return true;
        }
    }
}
