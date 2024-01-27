<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\EntryTimeHelper;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEventEntry;
use App\Models\Tenant\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompetitionMemberEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Competition $competition, Request $request)
    {
        $this->authorize('viewAny', CompetitionEntry::class);

        $entries = CompetitionEntry::where('competition_id', $competition->id)->whereHas('member');

        if ($request->string('sex') != '') {
            $entries = $entries->whereRelation('member', 'Gender', '=', $request->string('sex'));
        }

        $entries = $entries
            ->with([
                'member',
                'competitionEventEntries',
                'competitionEventEntries.competitionEvent',
            ])
            ->paginate(config('app.per_page'))
            ->withQueryString();

        $data = [
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
            'entries' => $entries->onEachSide(3),
        ];

        $entries->getCollection()->transform(function (CompetitionEntry $item) use ($competition) {
            return [
                'competition' => [
                    'id' => $competition->id,
                    'name' => $competition->name,
                ],
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
                    'id' => $item->member->MemberID,
                    'name' => $item->member->name,
                    'first_name' => $item->member->MForename,
                    'last_name' => $item->member->MSurname,
                    'date_of_birth' => $item->member->DateOfBirth,
                    'age' => $item->member->age(),
                    'age_on_day' => $item->member->ageAt($competition->age_at_date),
                    'sex' => $item->member->Gender,
                    'custom_fields' => [],
                ],
                'entries' => $item->competitionEventEntries->map(function (CompetitionEventEntry $entry) {
                    return [
                        'id' => $entry->id,
                        'amount' => $entry->amount,
                        'amount_refunded' => $entry->amount_refunded,
                        'amount_string' => $entry->amount_string,
                        'amount_refunded_string' => $entry->amount_refunded_string,
                        'cancellation_reason' => $entry->cancellation_reason,
                        'entry_time' => $entry->entry_time ? EntryTimeHelper::formatted($entry->entry_time) : null,
                        'notes' => $entry->notes,
                        'paid' => $entry->paid,
                        'refunded' => $entry->refunded,
                        'fully_refunded' => $entry->fully_refunded,
                        'event' => [
                            'id' => $entry->competitionEvent->id,
                            'name' => $entry->competitionEvent->name,
                        ],
                    ];
                }),
            ];
        });

        return Inertia::render('Competitions/Entries/MemberEntryList', $data);
    }

    public function show(Competition $competition, CompetitionEntry $entry)
    {
        $this->authorize('view', $entry);

        $entry->load([
            'member',
            'competitionEventEntries',
            'competitionEventEntries.competitionEvent',
            'member.user',
        ]);

        /** @var CompetitionEventEntry $firstEntry */
        $firstEntry = $entry->competitionEventEntries->first();
        /** @var Payment $payment */
        $payment = $firstEntry?->paymentLine?->payment;

        $data = [
            'id' => $entry->id,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
                'age_at_date' => $competition->age_at_date,
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
            'header' => [
                'name' => $entry->member->user->name,
                'email' => $entry->member->user->email,
            ],
            'entrant' => [
                'id' => $entry->member->MemberID,
                'name' => $entry->member->name,
                'first_name' => $entry->member->MForename,
                'last_name' => $entry->member->MSurname,
                'date_of_birth' => $entry->member->DateOfBirth,
                'age' => $entry->member->age(),
                'age_on_day' => $entry->member->ageAt($competition->age_at_date),
                'sex' => $entry->member->Gender,
                'custom_fields' => [],
            ],
            'entries' => $entry->competitionEventEntries->map(function (CompetitionEventEntry $entry) {
                return [
                    'id' => $entry->id,
                    'amount' => $entry->amount,
                    'amount_refunded' => $entry->amount_refunded,
                    'amount_string' => $entry->amount_string,
                    'amount_refunded_string' => $entry->amount_refunded_string,
                    'cancellation_reason' => $entry->cancellation_reason,
                    'entry_time' => $entry->entry_time ? EntryTimeHelper::formatted($entry->entry_time) : null,
                    'notes' => $entry->notes,
                    'paid' => $entry->paid,
                    'refunded' => $entry->refunded,
                    'fully_refunded' => $entry->fully_refunded,
                    'event' => [
                        'id' => $entry->competitionEvent->id,
                        'name' => $entry->competitionEvent->name,
                    ],
                ];
            }),
            'payment' => $payment ? [
                'id' => $payment->id,
                'stripe_id' => $payment->stripe_id,
                'stripe_status' => $payment->stripe_status,
                'stripe_fee' => $payment->stripe_fee,
                'amount' => $payment->amount,
                'formatted_amount' => $payment->formatted_amount,
                'payment_method' => $payment->paymentMethod ? [
                    'id' => $payment->paymentMethod->id,
                    'stripe_id' => $payment->paymentMethod->stripe_id,
                    'description' => $payment->paymentMethod->description,
                    'information_line' => $payment->paymentMethod->information_line,
                ] : null,
            ] : null,
            'created_at' => $entry->created_at,
            'updated_at' => $entry->updated_at,
        ];

        return Inertia::render('Competitions/Entries/MemberEntryReadOnly', $data);
    }
}
