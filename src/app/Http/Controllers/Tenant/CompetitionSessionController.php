<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\CompetitionMode;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionSession;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionSessionController extends Controller
{
    public function show(Competition $competition, CompetitionSession $session): Response
    {
        $venue = $session->venue ?? $competition->venue;

        return Inertia::render('Competitions/Sessions/Show', [
            'google_maps_api_key' => config('google.maps.clientside'),
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
                'basic_mode' => $competition->mode === CompetitionMode::BASIC,
            ],
            'venue' => [
                'id' => $venue->id,
                'name' => $venue->name,
                'place_id' => $venue->place_id,
                'formatted_address' => $venue->formatted_address,
            ],
            'events' => $session->events()->orderBy('sequence')->get()->toArray(),
            'sequence_number' => $session->sequence,
            'number_of_sessions' => $competition->sessions()->count(),
            'id' => $session->id,
            'name' => $session->name,
        ]);
    }
}
