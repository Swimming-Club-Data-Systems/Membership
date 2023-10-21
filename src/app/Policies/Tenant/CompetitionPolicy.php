<?php

namespace App\Policies\Tenant;

use App\Enums\CompetitionOpenTo;
use App\Enums\CompetitionStatus;
use App\Models\Tenant\Competition;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Carbon;

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

    public function enter(User $user, Competition $competition): Response
    {
        $isOpen = false;
        switch ($competition->status) {
            case CompetitionStatus::DRAFT:
            case CompetitionStatus::CANCELLED:
            case CompetitionStatus::CLOSED:
            case CompetitionStatus::PAUSED:
                $isOpen = false;
                break;
            case CompetitionStatus::PUBLISHED:
                $isOpen = true;
                break;
        }

        // Check open to members, if not
        if (! in_array($competition->open_to, [CompetitionOpenTo::MEMBERS, CompetitionOpenTo::MEMBERS_AND_GUESTS])) {
            return Response::deny('This competition is not open to club members.');
        }

        // Validate closing datetime
        if ($competition->closing_date < Carbon::now()) {
            return Response::deny('This competition is closed to new entries');
        }

        // Check if open to members
        return $isOpen ? Response::allow() : Response::denyAsNotFound();
    }

    public function enterAsGuest(?User $user, Competition $competition): Response
    {
        $isOpen = false;
        switch ($competition->status) {
            case CompetitionStatus::DRAFT:
            case CompetitionStatus::CANCELLED:
            case CompetitionStatus::CLOSED:
            case CompetitionStatus::PAUSED:
                $isOpen = false;
                break;
            case CompetitionStatus::PUBLISHED:
                $isOpen = true;
                break;
        }

        // Check open to guests, if not
        if (! in_array($competition->open_to, [CompetitionOpenTo::GUESTS, CompetitionOpenTo::MEMBERS_AND_GUESTS])) {
            return Response::deny('This competition is not open to guest entrants.');
        }

        // Validate closing datetime
        if ($competition->closing_date < Carbon::now()) {
            return Response::deny('This competition is closed to new entries');
        }

        return ($isOpen && $competition->public) ? Response::allow() : Response::denyAsNotFound();
    }

    public function update(User $user, Competition $competition)
    {
        // Handled in before
    }
}
