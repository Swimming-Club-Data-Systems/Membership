<?php

namespace App\Policies\Tenant\Auth;

use App\Models\Tenant\Auth\UserCredential;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserCredentialPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, UserCredential $userCredential)
    {
        return $user->UserID === $userCredential->user_id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, UserCredential $userCredential)
    {
        return $user->UserID === $userCredential->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, UserCredential $userCredential)
    {
        return $user->UserID === $userCredential->user_id;
    }
}
