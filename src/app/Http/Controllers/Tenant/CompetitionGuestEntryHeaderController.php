<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\Sex;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEvent;
use App\Models\Tenant\CompetitionGuestEntrant;
use App\Models\Tenant\CompetitionGuestEntryHeader;
use App\Models\Tenant\CompetitionSession;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

class CompetitionGuestEntryHeaderController extends Controller
{
    public function new(Competition $competition, Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Competitions/Entries/NewGuestEntryHeader', [
            'user' => $user != null ? [
                'first_name' => $user->Forename,
                'last_name' => $user->Surname,
                'email' => $user->email,
            ] : null,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
        ]);
    }

    public function create(Competition $competition, Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email:rfc,dns'],
            'swimmers' => [
                'first_name' => ['required', 'string', 'max:50'],
                'last_name' => ['required', 'string', 'max:50'],
                'date_of_birth' => ['required', 'date', 'after_or_equal:1900-01-01', 'before_or_equal:today'],
                'sex' => ['required', new Enum(Sex::class)],
            ],
        ]);

        DB::beginTransaction();

        try {
            $guestEntryHeader = new CompetitionGuestEntryHeader();
            if ($request->user()) {
                $guestEntryHeader->user()->associate($request->user());
            } else {
                $guestEntryHeader->first_name = $request->string('first_name');
                $guestEntryHeader->last_name = $request->string('last_name');
                $guestEntryHeader->email = $request->string('email');
            }
            $guestEntryHeader->save();

            $request->collect('swimmers')->each(function ($swimmer) use ($guestEntryHeader) {
                $entrant = new CompetitionGuestEntrant();
                $entrant->first_name = $swimmer['first_name'];
                $entrant->last_name = $swimmer['last_name'];
                $entrant->date_of_birth = $swimmer['date_of_birth'];
                $entrant->sex = $swimmer['sex'];
                $entrant->competitionGuestEntryHeader()->associate($guestEntryHeader);
                $entrant->save();
            });

            DB::commit();

            $request->session()->put('competition_guest_entry_header_id', $guestEntryHeader->id);

            return Redirect::route('competitions.enter_as_guest.show', [$competition, $guestEntryHeader]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Report error in flash message
            $request->session()->flash('error', 'An error occurred and we were unable to save your data. Please try again.');

            return Redirect::back();
        }

    }

    public function show(Competition $competition, CompetitionGuestEntryHeader $header, Request $request)
    {
        return Inertia::render('Competitions/Entries/GuestEntryShow', [
            'id' => $header->id,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
            'first_name' => $header->first_name,
            'last_name' => $header->last_name,
            'email' => $header->email,
            'entrants' => $header->competitionGuestEntrants()->get()->map(function (CompetitionGuestEntrant $entrant) use ($competition) {
                return [
                    'id' => $entrant->id,
                    'first_name' => $entrant->first_name,
                    'last_name' => $entrant->last_name,
                    'date_of_birth' => $entrant->date_of_birth,
                    'sex' => $entrant->sex,
                    'age' => $entrant->age,
                    'age_on_day' => $entrant->ageAt($competition->age_at_date),
                ];
            })->toArray(),
        ]);
    }

    public function editEntry(Competition $competition, CompetitionGuestEntryHeader $header, CompetitionGuestEntrant $entrant, Request $request)
    {
        return Inertia::render('Competitions/Entries/EditGuestEntry', [
            'id' => $header->id,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
            'entrant' => [
                'id' => $entrant->id,
                'first_name' => $entrant->first_name,
                'last_name' => $entrant->last_name,
                'date_of_birth' => $entrant->date_of_birth,
                'sex' => $entrant->sex,
                'age' => $entrant->age,
                'age_on_day' => $entrant->ageAt($competition->age_at_date),
            ],
            'sessions' => $competition
                ->sessions()
                ->get()
                ->map(function (CompetitionSession $session) use ($entrant, $competition) {
                    return [
                        'id' => $session->id,
                        'name' => $session->name,
                        'sequence' => $session->sequence,
                        'events' => $session
                            ->events()
                            ->orderBy('sequence', 'asc')
                            ->get()
                            ->filter(function (CompetitionEvent $event) use ($entrant, $competition) {
                                return $event->categoryMatches($entrant->sex) && $event->ageMatches($entrant->ageAt($competition->age_at_date));
                            })
                            ->map(function (CompetitionEvent $event) {
                                return [
                                    'id' => $event->id,
                                    'name' => $event->name,
                                    'sequence' => $event->sequence,
                                    'distance' => $event->distance,
                                    'stroke' => $event->stroke,
                                    'units' => $event->units,
                                    'event_code' => $event->event_code,
                                    'entry_fee' => $event->entry_fee,
                                    'entry_fee_string' => $event->entry_fee_string,
                                    'processing_fee' => $event->processing_fee,
                                    'processing_fee_string' => $event->processing_fee_string,
                                    'category' => $event->category,
                                ];
                            })
                            ->toArray(),
                    ];
                })
                ->toArray(),
        ]);
    }
}
