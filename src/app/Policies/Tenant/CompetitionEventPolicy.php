<?php

namespace App\Policies\Tenant;

use App\Enums\CompetitionStatus;
use App\Models\Tenant\CompetitionEvent;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CompetitionEventPolicy
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

    public function view(?User $user, CompetitionEvent $event): Response
    {
        $isPublished = $event->competition->status !== CompetitionStatus::DRAFT;
        if ($user) {
            return $isPublished ? Response::allow() : Response::denyAsNotFound();
        } else {
            return ($isPublished && $event->competition->public) ? Response::allow() : Response::denyAsNotFound();
        }
    }

    public function update(User $user, CompetitionEvent $event)
    {
        // Handled in before
    }

    public function delete(User $user, CompetitionEvent $event)
    {
        // Handled in before
    }
}
