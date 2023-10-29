<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\Venue;
use Illuminate\Auth\Access\Response;

class VenuePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function before(User $user): ?Response
    {
        if ($user->hasPermission(['Admin', 'Coach', 'Galas'])) {
            return Response::allow();
        }

        return null;
    }

    public function create(User $user)
    {
        // Handled in before
    }

    public function viewAny(?User $user): Response
    {
        return Response::allow();
    }

    public function view(?User $user, Venue $venue): Response
    {
        return Response::allow();
    }

    public function update(User $user, Venue $venue)
    {
        // Handled in before
    }
}
