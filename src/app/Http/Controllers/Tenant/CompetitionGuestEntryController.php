<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\EntryTimeHelper;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEventEntry;
use App\Models\Tenant\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CompetitionGuestEntryController extends Controller
{
    public function index(Competition $competition)
    {
        $this->authorize('viewAny', CompetitionEntry::class);

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
                    'id' => $item->competitionGuestEntrant->id,
                    'name' => $item->competitionGuestEntrant->name,
                    'first_name' => $item->competitionGuestEntrant->first_name,
                    'last_name' => $item->competitionGuestEntrant->last_name,
                    'date_of_birth' => $item->competitionGuestEntrant->date_of_birth,
                    'age' => $item->competitionGuestEntrant->age,
                    'age_on_day' => $item->competitionGuestEntrant->ageAt($competition->age_at_date),
                    'sex' => $item->competitionGuestEntrant->sex,
                    'custom_fields' => $item->competitionGuestEntrant->getCustomFieldData($competition),
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

        return Inertia::render('Competitions/Entries/GuestEntryList', $data);
    }

    public function show(Competition $competition, CompetitionEntry $entry)
    {
        $this->authorize('view', $entry);

        $entry->load([
            'competitionGuestEntrant',
            'competitionEventEntries',
            'competitionEventEntries.competitionEvent',
            'competitionGuestEntrant.competitionGuestEntryHeader',
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
                'id' => $entry->competitionGuestEntrant->competitionGuestEntryHeader->id,
                'name' => $entry->competitionGuestEntrant->competitionGuestEntryHeader->name,
                'email' => $entry->competitionGuestEntrant->competitionGuestEntryHeader->email,
            ],
            'entrant' => [
                'id' => $entry->competitionGuestEntrant->id,
                'name' => $entry->competitionGuestEntrant->name,
                'first_name' => $entry->competitionGuestEntrant->first_name,
                'last_name' => $entry->competitionGuestEntrant->last_name,
                'date_of_birth' => $entry->competitionGuestEntrant->date_of_birth,
                'age' => $entry->competitionGuestEntrant->age,
                'age_on_day' => $entry->competitionGuestEntrant->ageAt($competition->age_at_date),
                'sex' => $entry->competitionGuestEntrant->sex,
                'custom_fields' => $entry->competitionGuestEntrant->getCustomFieldData($competition),
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

        return Inertia::render('Competitions/Entries/GuestEntryReadOnly', $data);
    }

    public function update(Competition $competition, CompetitionEntry $entry, Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $entry);

        $request->validate([
            'approved' => ['boolean'],
            'paid' => ['boolean'],
            'processed' => ['boolean'],
        ]);

        $entry->approved = $request->boolean('approved');
        $entry->processed = $request->boolean('processed');
        if (! $entry->paid) {
            // Entries can not be marked as unpaid
            $entry->paid = $request->boolean('paid');
        }

        $entry->save();

        $returnUrl = $request->string('return_url');
        if (Str::length($returnUrl) > 0) {
            // Redirect to return_url if provided, e.g. for if editing from the entry list
            $request->session()->flash('flash_bag.'.$entry->id.'.success', 'Changes saved successfully.');

            return Redirect::to($request->string('return_url'));
        }

        $request->session()->flash('success', 'Changes saved successfully.');

        return Redirect::route('competitions.guest_entries.show', [$competition, $entry]);
    }
}
