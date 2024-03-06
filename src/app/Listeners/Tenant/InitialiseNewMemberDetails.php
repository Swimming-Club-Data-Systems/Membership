<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MemberCreating;

class InitialiseNewMemberDetails
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
    public function handle(MemberCreating $event): void
    {
        $event->member->AccessKey = \Str::random(16);
    }
}
