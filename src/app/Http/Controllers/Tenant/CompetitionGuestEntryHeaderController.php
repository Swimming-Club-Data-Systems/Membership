<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\Sex;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEvent;
use App\Models\Tenant\CompetitionEventEntry;
use App\Models\Tenant\CompetitionGuestEntrant;
use App\Models\Tenant\CompetitionGuestEntryHeader;
use App\Models\Tenant\CompetitionSession;
use App\Models\Tenant\User;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
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
        // Get existing entries
        /** @var CompetitionEntry $entry */
        $entry = CompetitionEntry::where('competition_guest_entrant_id', '=', $entrant->id)->with('events')->first();

        $sessions = $competition
            ->sessions()
            ->with('events')
            ->get();

        $swimsFormData = [];

        $sessionData = $sessions
            ->map(function (CompetitionSession $session) use ($entrant, $competition, $entry, &$swimsFormData) {
                return [
                    'id' => $session->id,
                    'name' => $session->name,
                    'sequence' => $session->sequence,
                    'events' => $session
                        ->events
                        ->filter(function (CompetitionEvent $event) use ($entrant, $competition) {
                            return $event->categoryMatches($entrant->sex) && $event->ageMatches($entrant->ageAt($competition->age_at_date));
                        })
                        ->map(function (CompetitionEvent $event) use ($entry, &$swimsFormData) {

                            // Get event entry if exists
                            /** @var CompetitionEventEntry $eventEntry */
                            $eventEntry = $entry?->events->where('competition_event_id', '=', $event->id)->first();

                            $swimsFormData[] = [
                                'event_id' => $event->id,
                                'sequence' => $event->sequence,
                                'entering' => $eventEntry != null,
                                'entry_time' => $eventEntry?->entry_time,
                                'id' => $eventEntry?->id,
                            ];

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
                                'event_entry' => $eventEntry ? [
                                    'id' => $eventEntry->id,
                                    'entry_time' => $eventEntry->entry_time,
                                    'amount' => $eventEntry->amount,
                                    'amount_refunded' => $eventEntry->amount_refunded,
                                    'cancellation_reason' => $eventEntry->cancellation_reason,
                                    'notes' => $eventEntry->notes,
                                ] : null,
                            ];
                        })
                        ->toArray(),
                ];
            })
            ->toArray();

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
            'sessions' => $sessionData,
            'form_initial_values' => [
                'entries' => $swimsFormData,
            ],
        ]);
    }

    public function updateEntry(Competition $competition, CompetitionGuestEntryHeader $header, CompetitionGuestEntrant $entrant, Request $request)
    {
        // Get existing entries
        /** @var CompetitionEntry $entry */
        $entry = CompetitionEntry::firstOrCreate(
            [
                'competition_guest_entrant_id' => $entrant->id,
                'competition_id' => $competition->id,
            ]
        );

        if ($entry->locked) {
            // return, can't edit
        }

        $validated = $request->validate([
            'entries.*.entering' => ['boolean'],
            'entries.*.id' => [
                'nullable',
                Rule::exists('competition_event_entries', 'id')->where(function (Builder $query) use ($entry) {
                    return $query->where('competition_entry_id', $entry->id);
                }),
            ],
            'entries.*.event_id' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail) use ($competition) {
                    $doesntExist = DB::table('competition_events')
                        ->join('competition_sessions', 'competition_events.competition_session_id', '=', 'competition_sessions.id')
                        ->where('competition_events.id', $value)
                        ->where('competition_sessions.competition_id', $competition->id)
                        ->doesntExist();

                    if ($doesntExist) {
                        $fail("The {$attribute} is invalid.");
                    }
                },
            ],
            'entries.*.entry_time' => [
                'nullable',
            ],
        ]);

        foreach ($validated['entries'] as $event) {
            if ($event['entering']) {
                // Create or update
                $eventEntry = CompetitionEventEntry::firstOrNew(
                    [
                        'competition_entry_id' => $entry->id,
                        'competition_event_id' => $event['event_id'],
                    ],
                    [
                        'entry_time' => $event['entry_time'],
                    ]
                );

                // In future add logic for amending price before save

                $eventEntry->save();

            } else {
                // Delete
                if ($event['id']) {
                    CompetitionEventEntry::destroy($event['id']);
                }
            }
        }

        // Sum the totals
        $entry->calculateTotals();
        $entry->save();

    }
}
