<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CompetitionEntryPolicy
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

    public function viewAny(?User $user)
    {

    }

    public function view(?User $user, CompetitionEntry $entry)
    {
        if ($entry->member?->user->UserID === $user->UserID) {
            return true;
        }
    }

    public function update(?User $user, CompetitionEntry $entry)
    {
        if (! $entry->editable) {
            return false;
        }

        if ($entry->locked) {
            return false;
        }

        if ($entry->processed) {
            return false;
        }

        if ($entry->member?->user->UserID === $user->UserID) {
            return true;
        }
    }

    public function veto(?User $user, CompetitionEntry $entry)
    {
        if (! $entry->editable) {
            return false;
        }

        if (! $entry->vetoable) {
            return false;
        }

        if ($entry->processed) {
            return false;
        }

        if ($entry->member?->user->UserID === $user->UserID) {
            return true;
        }
    }
}
