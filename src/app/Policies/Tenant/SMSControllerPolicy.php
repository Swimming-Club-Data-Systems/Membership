<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SMSControllerPolicy
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
}
