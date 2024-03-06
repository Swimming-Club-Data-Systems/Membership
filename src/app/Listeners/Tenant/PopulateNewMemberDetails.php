<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MemberCreated;
use App\Models\Central\Tenant;
use Illuminate\Support\Str;

class PopulateNewMemberDetails
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MemberCreated $event): void
    {
        /** @var Tenant $tenant */
        $tenant = tenant();
        if (Str::length($event->member->ASANumber) < 1) {
            $event->member->ASANumber = $tenant->Code.$event->member->MemberID;
        }
        $event->member->save();
    }
}
