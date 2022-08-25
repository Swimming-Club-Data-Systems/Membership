<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\NotifyAdditionalEmail;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotifyAdditionalEmailPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param string $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if ($user->permissions()->where('Permission', 'Admin')->first()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\Tenant\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\Tenant\User $user
     * @param \App\Models\Tenant\NotifyAdditionalEmail $notifyAdditionalEmail
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, NotifyAdditionalEmail $notifyAdditionalEmail)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\Tenant\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\Tenant\User $user
     * @param \App\Models\Tenant\NotifyAdditionalEmail $notifyAdditionalEmail
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, NotifyAdditionalEmail $notifyAdditionalEmail)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\Tenant\User $user
     * @param \App\Models\Tenant\NotifyAdditionalEmail $notifyAdditionalEmail
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, NotifyAdditionalEmail $notifyAdditionalEmail)
    {
        return $user->UserID === $notifyAdditionalEmail->UserID;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\Tenant\User $user
     * @param \App\Models\Tenant\NotifyAdditionalEmail $notifyAdditionalEmail
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, NotifyAdditionalEmail $notifyAdditionalEmail)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\Tenant\User $user
     * @param \App\Models\Tenant\NotifyAdditionalEmail $notifyAdditionalEmail
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, NotifyAdditionalEmail $notifyAdditionalEmail)
    {
        //
    }
}
