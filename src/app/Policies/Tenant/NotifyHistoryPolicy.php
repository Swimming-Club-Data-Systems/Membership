<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class NotifyHistoryPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->hasPermission(['Admin', 'Coach', 'Galas'])) {
            return Response::allow();
        }
    }

    public function view(User $user)
    {
        if ($user->hasPermission(['Admin', 'Coach', 'Galas'])) {
            return Response::allow();
        } else {
            return Response::denyAsNotFound();
        }
    }
}
