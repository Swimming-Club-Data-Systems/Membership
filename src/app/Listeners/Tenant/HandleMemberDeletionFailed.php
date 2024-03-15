<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MemberDeletionFailed;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleMemberDeletionFailed implements ShouldQueue
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
    public function handle(MemberDeletionFailed $event): void
    {
        //
    }
}
