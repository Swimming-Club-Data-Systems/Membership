<?php

namespace App\Listeners\Tenant;

use App\Enums\Queue;
use App\Events\Tenant\CompetitionEntryVetoed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendCompetitionEntryVetoedNotification implements ShouldQueue
{
    public string $queue = Queue::NOTIFY->value;

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
    public function handle(CompetitionEntryVetoed $event): void
    {
        if ($event->entry->member?->user) {
            // Send an email detailing the squad move
            Mail::to($event->entry->member->user)->send(new \App\Mail\Competitions\CompetitionEntryVetoed($event->entry));
        }
    }
}
