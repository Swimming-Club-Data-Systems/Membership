<?php

namespace App\Policies\Central\Auth;

use App\Models\Central\Auth\UserCredential;
use App\Models\Central\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserCredentialPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Central\User  $user
     * @param  \App\Models\Central\Auth\UserCredential  $userCredential
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, UserCredential $userCredential)
    {
        return $user->id === $userCredential->user_id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Central\User  $user
     * @param  \App\Models\Central\Auth\UserCredential  $userCredential
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, UserCredential $userCredential)
    {
        return $user->id === $userCredential->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Central\User  $user
     * @param  \App\Models\Central\Auth\UserCredential  $userCredential
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, UserCredential $userCredential)
    {
        return $user->id === $userCredential->user_id;
    }
}
