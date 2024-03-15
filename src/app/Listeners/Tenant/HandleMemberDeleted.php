<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MemberDeletionCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleMemberDeleted implements ShouldQueue
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
    public function handle(MemberDeletionCompleted $event): void
    {
        //
    }
}
