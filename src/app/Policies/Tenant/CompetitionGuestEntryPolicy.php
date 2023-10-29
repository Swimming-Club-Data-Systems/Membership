<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CompetitionGuestEntryHeader;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CompetitionGuestEntryPolicy
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
        if ($user->hasPermission(['Admin', 'Galas'])) {
            return Response::allow();
        }

        return null;
    }

    public function create(): Response
    {
        return Response::allow();
    }

    public function view(?User $user, CompetitionGuestEntryHeader $header): Response
    {
        if ($user) {
            return $user->id === $header->user?->id ? Response::allow() : Response::denyAsNotFound();
        }

        return Response::denyAsNotFound();
    }

    public function update(?User $user, CompetitionGuestEntryHeader $header): Response
    {
        if ($user) {
            return $user->id === $header->user?->id ? Response::allow() : Response::denyAsNotFound();
        }

        return Response::allow();
    }
}
