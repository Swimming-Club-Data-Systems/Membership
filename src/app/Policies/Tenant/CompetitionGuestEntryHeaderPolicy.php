<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CompetitionGuestEntryHeader;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CompetitionGuestEntryHeaderPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function before(?User $user): ?Response
    {
        if ($user?->hasPermission(['Admin', 'Galas'])) {
            return Response::allow();
        }

        return null;
    }

    public function create(?User $user): Response
    {
        return Response::allow();
    }

    private function viewUpdate(?User $user, CompetitionGuestEntryHeader $header): Response
    {
        // If user, check ids
        if ($user) {
            return $user->id === $header->user?->id ? Response::allow() : Response::denyAsNotFound();
        }

        // Else if guest, check header id is in competition_guest_entry_header_ids array
        $inSession = in_array($header->id, session('competition_guest_entry_header_ids', []));
        if ($inSession) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function view(?User $user, CompetitionGuestEntryHeader $header): Response
    {
        return $this->viewUpdate($user, $header);
    }

    public function update(?User $user, CompetitionGuestEntryHeader $header): Response
    {
        return $this->viewUpdate($user, $header);
    }
}
