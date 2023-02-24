<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\ManualPaymentEntry;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManualPaymentEntryPolicy
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

    public function create(User $user): true|\Illuminate\Auth\Access\Response
    {
        return $user->hasPermission(['Admin']) ? true : $this->denyAsNotFound();
    }

    public function amend(User $user, ManualPaymentEntry $entry): true|\Illuminate\Auth\Access\Response
    {
        return $user->id === $entry->user?->id ? true : $this->denyAsNotFound();
    }
}
