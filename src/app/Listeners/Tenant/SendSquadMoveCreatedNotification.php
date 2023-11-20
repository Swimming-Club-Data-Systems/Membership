<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\SquadMoveCreated;
use Illuminate\Support\Facades\Mail;

class SendSquadMoveCreatedNotification
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
    public function handle(SquadMoveCreated $event): void
    {
        if ($event->squadMove->member->user) {
            // Send an email detailing the squad move
            Mail::to($event->squadMove->member->user)->send(new \App\Mail\SquadMoveCreated($event->squadMove));
        }
    }
}
