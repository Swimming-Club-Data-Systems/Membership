<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\CompetitionFileDeleted;
use Illuminate\Support\Facades\Storage;

class DeleteCompetitionFileFromStorage
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
    public function handle(CompetitionFileDeleted $event): void
    {
        Storage::disk($event->competitionFile->disk)->delete($event->competitionFile->path);
    }
}
