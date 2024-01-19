<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\EntryTimeHelper;
use App\Business\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEvent;
use App\Models\Tenant\CompetitionEventEntry;
use App\Models\Tenant\CompetitionSession;
use App\Models\Tenant\Member;
use App\Models\Tenant\User;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CompetitionMemberEntryHeaderController extends Controller
{
    public function new(Competition $competition, Request $request)
    {
        $this->authorize('enter', $competition);

        /** @var User $user */
        $user = $request->user();

        // Check if the user has just one member, if so go to that member/entry
        if ($user->members()->count() === 1) {
            return redirect(route('competitions.enter.edit_entry', [$competition, $user->members->first()]));
        } else {
            // Else show the member selection page
            return Inertia::render('Competitions/Entries/NewMemberEntrySelect', [
                'user' => $user != null ? [
                    'first_name' => $user->Forename,
                    'last_name' => $user->Surname,
                    'email' => $user->email,
                ] : null,
                'competition' => [
                    'id' => $competition->id,
                    'name' => $competition->name,
                ],
                // 'custom_fields' => $competition->custom_fields,
                'members' => $user
                    ->members()
                    ->orderBy('MForename', 'asc')
                    ->orderBy('MSurname', 'asc')
                    ->get()
                    ->map(fn (Member $member) => [
                        'id' => $member->MemberID,
                        'name' => $member->name,
                        'age' => $member->age(),
                        'date_of_birth' => $member->DateOfBirth,
                        'paid' => false,
                        'formatted_amount' => Money::formatCurrency(0, 'GBP'),
                    ]),
            ]);
        }
    }

    public function editEntry(Competition $competition, Member $member, Request $request)
    {
        $entry = CompetitionEntry::where('member_MemberID', '=', $member->MemberID)
            ->where('competition_id', '=', $competition->id)
            ->first();

        if ($competition->coach_enters && ! $entry) {
            // Show select session availability form if the coach enters and an entry does not yet exist
            return $this->editEntrySelectSessions($competition, $member);
        } else {
            return $this->editEntryWithEntry($competition, $member, $entry);
        }
    }

    private function editEntrySelectSessions(Competition $competition, Member $member)
    {
        $sessions = $competition
            ->sessions()
            ->with('events')
            ->get();

        $sessionsFormData = [];

        $sessionData = $sessions
            ->map(function (CompetitionSession $session) use ($member, $competition, &$sessionsFormData) {
                return [
                    'id' => $session->id,
                    'name' => $session->name,
                    'sequence' => $session->sequence,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'timezone' => $session->timezone,
                    'events' => $session
                        ->events
                        ->filter(function (CompetitionEvent $event) use ($member, $competition) {
                            return $event->categoryMatches($member->Gender) && $event->ageMatches($member->ageAt($competition->age_at_date));
                        })
                        ->map(function (CompetitionEvent $event) use (&$sessionsFormData) {
                            $sessionsFormData[] = [
                                'event_id' => $event->id,
                                'sequence' => $event->sequence,
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
                                'entry_fee_formatted' => Money::formatCurrency($event->entry_fee),
                                'processing_fee' => $event->processing_fee,
                                'processing_fee_string' => $event->processing_fee_string,
                                'processing_fee_formatted' => Money::formatCurrency($event->processing_fee),
                                'category' => $event->category,
                            ];
                        })
                        ->values(),
                ];
            })
            ->toArray();

        return Inertia::render('Competitions/Entries/EditMemberSessionAvailability', [
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
                'require_times' => $competition->require_times,
                'processing_fee' => $competition->processing_fee,
                'processing_fee_formatted' => Money::formatCurrency($competition->processing_fee),
            ],
            'entrant' => [
                'id' => $member->MemberID,
                'first_name' => $member->MForename,
                'last_name' => $member->MSurname,
                'date_of_birth' => $member->DateOfBirth,
                'sex' => $member->Gender,
                'age' => $member->age(),
                'age_on_day' => $member->ageAt($competition->age_at_date),
            ],
            'sessions' => $sessionData,
            'form_initial_values' => [
                'sessions' => $sessionsFormData,
            ],
        ]);
    }

    private function editEntryWithEntry(Competition $competition, Member $member, ?CompetitionEntry $entry)
    {
        // Show competition entry form

        if (! $entry) {
            $entry = CompetitionEntry::create([
                'member_MemberID' => $member->MemberID,
                'competition_id' => $competition->id,
            ]);
        }

        $sessions = $competition
            ->sessions()
            ->with('events')
            ->get();

        $swimsFormData = [];

        $sessionData = $sessions
            ->map(function (CompetitionSession $session) use ($member, $competition, $entry, &$swimsFormData) {
                return [
                    'id' => $session->id,
                    'name' => $session->name,
                    'sequence' => $session->sequence,
                    'events' => $session
                        ->events
                        ->filter(function (CompetitionEvent $event) use ($member, $competition) {
                            return $event->categoryMatches($member->Gender) && $event->ageMatches($member->ageAt($competition->age_at_date));
                        })
                        ->map(function (CompetitionEvent $event) use ($entry, &$swimsFormData) {

                            // Get event entry if exists
                            /** @var CompetitionEventEntry $eventEntry */
                            $eventEntry = $entry?->competitionEventEntries->where('competition_event_id', '=', $event->id)->first();

                            $swimsFormData[] = [
                                'event_id' => $event->id,
                                'sequence' => $event->sequence,
                                'entering' => $eventEntry != null,
                                'entry_time' => $eventEntry?->entry_time ? EntryTimeHelper::formatted($eventEntry?->entry_time) : null,
                                'amount' => $eventEntry ? $eventEntry->amount_string : Money::formatDecimal($event->entry_fee + $event->processing_fee),
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
                        ->values(),
                ];
            })
            ->toArray();

        return Inertia::render('Competitions/Entries/EditMemberEntry', [
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
                'require_times' => $competition->require_times,
            ],
            'entrant' => [
                'id' => $member->MemberID,
                'first_name' => $member->MForename,
                'last_name' => $member->MSurname,
                'date_of_birth' => $member->DateOfBirth,
                'sex' => $member->Gender,
                'age' => $member->age(),
                'age_on_day' => $member->ageAt($competition->age_at_date),
            ],
            'sessions' => $sessionData,
            'form_initial_values' => [
                'entries' => $swimsFormData,
            ],
            'paid' => $entry?->paid,
        ]);
    }

    public function updateEntry(Competition $competition, Member $member, Request $request)
    {
        $entry = CompetitionEntry::firstOrCreate([
            'member_MemberID' => $member->MemberID,
            'competition_id' => $competition->id,
        ]);

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
            'entries' => ['array'],
        ]);

        if (Arr::isList($validated['entries'])) {
            foreach ($validated['entries'] as $event) {
                if ($event['entering']) {

                    // Handle entry time
                    $time = EntryTimeHelper::toDecimal($event['entry_time']);

                    // Create or update
                    $eventEntry = CompetitionEventEntry::firstOrNew(
                        [
                            'competition_entry_id' => $entry->id,
                            'competition_event_id' => $event['event_id'],
                        ]
                    );

                    $eventEntry->entry_time = $time;

                    if (! $eventEntry->exists) {
                        /** @var CompetitionEvent $competitionEvent */
                        $competitionEvent = $competition->events->find($event['event_id']);
                        $eventEntry->populateFromEvent($competitionEvent);

                        // If the user has permission,
                        // add logic for amending price before save
                    }

                    $eventEntry->save();

                } else {
                    // Delete
                    if ($event['id']) {
                        CompetitionEventEntry::destroy($event['id']);
                    }
                }
            }
        }

        // Sum the totals
        $entry->calculateTotals();
        $entry->save();

        $request->session()->flash('success', 'Changes to '.$member->name.'\'s entry saved successfully.');

        return Redirect::route('competitions.enter.edit_entry', [$competition, $member]);
    }
}
