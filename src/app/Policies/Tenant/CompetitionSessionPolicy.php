<?php

namespace App\Policies\Tenant;

use App\Enums\CompetitionStatus;
use App\Models\Tenant\CompetitionSession;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CompetitionSessionPolicy
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

    public function view(?User $user, CompetitionSession $session): Response
    {
        $isPublished = $session->competition->status !== CompetitionStatus::DRAFT;
        if ($user) {
            return $isPublished ? Response::allow() : Response::denyAsNotFound();
        } else {
            return ($isPublished && $session->competition->public) ? Response::allow() : Response::denyAsNotFound();
        }
    }

    public function update(User $user, CompetitionSession $session)
    {
        // Handled in before
    }
}
