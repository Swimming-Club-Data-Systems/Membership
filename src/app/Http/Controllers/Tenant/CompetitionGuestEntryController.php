<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEventEntry;
use Inertia\Inertia;

class CompetitionGuestEntryController extends Controller
{
    public function index(Competition $competition)
    {
        $entries = CompetitionEntry::where('competition_id', $competition->id)
            ->with(['competitionGuestEntrant', 'competitionEventEntries', 'competitionEventEntries.competitionEvent'])
            ->paginate(config('app.per_page'));

        $data = [
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
            'entries' => $entries->onEachSide(3),
        ];

        $entries->getCollection()->transform(function (CompetitionEntry $item) {
            return [
                'id' => $item->id,
                'amount' => $item->amount,
                'amount_string' => $item->amount_string,
                'amount_refunded' => $item->amount_refunded,
                'amount_refunded_string' => $item->amount_refunded_string,
                'approved' => $item->approved,
                'locked' => $item->locked,
                'paid' => $item->paid,
                'processed' => $item->processed,
                'processing_fee_paid' => $item->processing_fee_paid,
                'vetoable' => $item->vetoable,
                'entrant' => [
                    'id' => $item->competitionGuestEntrant->id,
                    'name' => $item->competitionGuestEntrant->name,
                    'first_name' => $item->competitionGuestEntrant->first_name,
                    'last_name' => $item->competitionGuestEntrant->last_name,
                    'date_of_birth' => $item->competitionGuestEntrant->date_of_birth,
                    'age' => $item->competitionGuestEntrant->age,
                    'sex' => $item->competitionGuestEntrant->sex,
                ],
                'entries' => $item->competitionEventEntries->map(function (CompetitionEventEntry $entry) {
                    return [
                        'id' => $entry->id,
                        'amount' => $entry->amount,
                        'amount_refunded' => $entry->amount_refunded,
                        'amount_string' => $entry->amount_string,
                        'amount_refunded_string' => $entry->amount_refunded_string,
                        'cancellation_reason' => $entry->cancellation_reason,
                        'entry_time' => $entry->entry_time,
                        'notes' => $entry->notes,
                        'paid' => $entry->paid,
                        'event' => [
                            'id' => $entry->competitionEvent->id,
                            'name' => $entry->competitionEvent->name,
                        ],
                    ];
                }),
            ];
        });

        return Inertia::render('Competitions/Entries/GuestEntryList', $data);
    }

    public function show(Competition $competition, CompetitionEntry $entry)
    {
        $entry->load([
            'competitionGuestEntrant',
            'competitionEventEntries',
            'competitionEventEntries.competitionEvent',
        ]);

        $data = [
            'id' => $entry->id,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
            'amount' => $entry->amount,
            'amount_string' => $entry->amount_string,
            'amount_refunded' => $entry->amount_refunded,
            'amount_refunded_string' => $entry->amount_refunded_string,
            'approved' => $entry->approved,
            'locked' => $entry->locked,
            'paid' => $entry->paid,
            'processed' => $entry->processed,
            'processing_fee_paid' => $entry->processing_fee_paid,
            'vetoable' => $entry->vetoable,
            'entrant' => [
                'id' => $entry->competitionGuestEntrant->id,
                'name' => $entry->competitionGuestEntrant->name,
                'first_name' => $entry->competitionGuestEntrant->first_name,
                'last_name' => $entry->competitionGuestEntrant->last_name,
                'date_of_birth' => $entry->competitionGuestEntrant->date_of_birth,
                'age' => $entry->competitionGuestEntrant->age,
                'sex' => $entry->competitionGuestEntrant->sex,
            ],
            'entries' => $entry->competitionEventEntries->map(function (CompetitionEventEntry $entry) {
                return [
                    'id' => $entry->id,
                    'amount' => $entry->amount,
                    'amount_refunded' => $entry->amount_refunded,
                    'amount_string' => $entry->amount_string,
                    'amount_refunded_string' => $entry->amount_refunded_string,
                    'cancellation_reason' => $entry->cancellation_reason,
                    'entry_time' => $entry->entry_time,
                    'notes' => $entry->notes,
                    'paid' => $entry->paid,
                    'event' => [
                        'id' => $entry->competitionEvent->id,
                        'name' => $entry->competitionEvent->name,
                    ],
                ];
            }),
        ];

        return Inertia::render('Competitions/Entries/GuestEntryReadOnly', $data);
    }
}
