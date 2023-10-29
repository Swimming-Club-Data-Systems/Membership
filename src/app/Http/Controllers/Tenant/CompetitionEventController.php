<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\CompetitionCategory;
use App\Enums\DistanceUnits;
use App\Enums\Stroke;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEvent;
use App\Models\Tenant\CompetitionSession;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CompetitionEventController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function create(Competition $competition, CompetitionSession $session, Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', CompetitionEvent::class);

        $validated = $request->validate([
            'name' => ['required'],
            'category' => ['required', new Enum(CompetitionCategory::class)],
            'stroke' => ['required', new Enum(Stroke::class)],
            'distance' => ['required', 'numeric', 'min:0', 'decimal:0,5'],
            'units' => ['required', new Enum(DistanceUnits::class)],
            'entry_fee_string' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'processing_fee_string' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'ages' => ['required', 'array', 'min:1'],
        ]);

        $event = new CompetitionEvent($validated);

        $event->session()->associate($session);
        $event->sequence = $session->events()->max('sequence') + 1;

        $event->save();

        // Flash message

        return redirect()->route('competitions.sessions.edit', [$competition, $session]);
    }

    /**
     * @throws AuthorizationException
     */
    public function delete(Competition $competition, CompetitionSession $session, CompetitionEvent $event): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        // Flash message

        return redirect()->route('competitions.sessions.edit', [$competition, $session]);
    }
}
