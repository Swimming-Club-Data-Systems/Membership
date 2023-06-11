<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\CompetitionMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CompetitionSessionPutRequest;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionSession;
use App\Models\Tenant\Venue;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionSessionController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function show(Competition $competition, CompetitionSession $session, Request $request): Response
    {
        $this->authorize('view', $session);

        $data = [
            ...$this->getData($competition, $session),
            'editable' => $request->user()?->can('update', $session),
            'edit_mode' => false,
        ];

        return Inertia::render('Competitions/Sessions/Show', $data);
    }

    private function getData(Competition $competition, CompetitionSession $session): array
    {
        $venue = $session->venue ?? $competition->venue;

        return [
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
            'different_venue_to_competition_venue' => $venue->id != $competition->venue->id,
            'start_time' => $session->start_time,
            'end_time' => $session->end_time,
        ];
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Competition $competition, CompetitionSession $session): Response
    {
        $this->authorize('update', $session);

        $venue = $session->venue ?? $competition->venue;

        $data = [
            ...$this->getData($competition, $session),
            'editable' => true,
            'edit_mode' => true,
            'edit_session' => [
                'form_initial_values' => [
                    'name' => $session->name,
                    'start_date' => $session->start_time,
                    'end_date' => $session->end_time,
                    //                    'start_date' => $session->start_time->toDateString(),
                    //                    'start_time' => $session->start_time->toTimeString(),
                    //                    'end_date' => $session->end_time->toDateString(),
                    //                    'end_time' => $session->end_time->toTimeString(),
                    'venue' => $venue->id,
                ],
            ],
        ];

        return Inertia::render('Competitions/Sessions/Show', $data);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Competition $competition, CompetitionSession $session, CompetitionSessionPutRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $session);

        $validated = $request->validated();

        $session->fill($validated);

        /** @var Venue $venue */
        $venue = Venue::find($request->integer('venue'));
        if ($venue->id != $competition->venue->id) {
            $session->venue()->associate($venue);
        } else {
            $session->venue()->dissociate();
        }

        $session->save();

        return redirect()->route('competitions.sessions.edit', [$competition, $session]);
    }
}
