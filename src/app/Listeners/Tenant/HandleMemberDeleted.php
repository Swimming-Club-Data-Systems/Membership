<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MemberDeletionCompleted;
use App\Mail\Members\DeletionSuccessful;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

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
        Mail::to($event->deletedFor)->send(new DeletionSuccessful($event->deletedFor, $event->memberName));
    }
}
