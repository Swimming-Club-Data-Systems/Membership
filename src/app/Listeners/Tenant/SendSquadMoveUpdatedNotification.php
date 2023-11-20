<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\SquadMoveUpdated;
use Illuminate\Support\Facades\Mail;

class SendSquadMoveUpdatedNotification
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
    public function handle(SquadMoveUpdated $event): void
    {
        if ($event->squadMove->member->user) {
            // Send an email detailing the squad move
            Mail::to($event->squadMove->member->user)->send(new \App\Mail\SquadMoveUpdated($event->squadMove));
        }
    }
}
