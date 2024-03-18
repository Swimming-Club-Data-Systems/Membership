<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\EmergencyContact;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class EmergencyContactPolicy
{
    /**
     * Create a new policy instance.
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

    public function create(User $user)
    {
        // All users can create
        return true;
    }

    public function update(User $user, EmergencyContact $contact)
    {
        return $user->UserID === $contact->UserID;
    }

    public function delete(User $user, EmergencyContact $contact)
    {
        return $user->UserID === $contact->UserID;
    }
}
