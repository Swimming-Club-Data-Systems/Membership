<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MemberDeletionFailed;
use App\Mail\Members\DeletionFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

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
        Mail::to($event->deletingFor)->send(new DeletionFailed($event->deletingFor, $event->member));
    }
}
