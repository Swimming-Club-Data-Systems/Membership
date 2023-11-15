<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\SquadMoveDeleted;
use Illuminate\Support\Facades\Mail;

class SendSquadMoveDeletedNotification
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
    public function handle(SquadMoveDeleted $event): void
    {
        if ($event->squadMove->member->user) {
            // Send an email detailing the squad move
            Mail::to($event->squadMove->member->user)->send(new \App\Mail\SquadMoveDeleted($event->squadMove));
        }
    }
}
