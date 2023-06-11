<?php

namespace App\Policies\Tenant;

use App\Enums\CompetitionStatus;
use App\Models\Tenant\Competition;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CompetitionPolicy
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

    public function viewAny(?User $user)
    {

    }

    public function view(?User $user, Competition $competition): Response
    {
        $isPublished = $competition->status !== CompetitionStatus::DRAFT;
        if ($user) {
            return $isPublished ? Response::allow() : Response::denyAsNotFound();
        } else {
            return ($isPublished && $competition->public) ? Response::allow() : Response::denyAsNotFound();
        }
    }

    public function update(User $user, Competition $competition)
    {
        // Handled in before
    }
}
