<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Enums\CompetitionEntryCancellationReason;
use App\Http\Controllers\Controller;
use App\Interfaces\PaidObject;
use App\Mail\Payments\PaymentRefunded;
use App\Models\Central\Tenant;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEventEntry;
use App\Models\Tenant\Member;
use App\Models\Tenant\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CompetitionMemberEntryRejectionController extends Controller
{
    public function index(Competition $competition)
    {
        $this->authorize('viewAny', CompetitionEntry::class);

        $members = DB::table('competition_entries')
            ->join('members', 'competition_entries.member_MemberID', '=', 'members.MemberID')
            ->where('competition_entries.competition_id', '=', $competition->id)
            ->select('member_MemberID')
            ->orderBy('members.MSurname', 'asc')
            ->orderBy('members.MForename', 'asc')
            ->distinct()
            ->paginate();

        $ids = collect($members->items())->map(fn ($item) => $item->member_MemberID);

        //        $memberModels = Member::findMany($ids);

        $memberModels = Member::whereIn('MemberID', $ids)->with([
            'competitionEntries' => function ($q) use ($competition) {
                $q->where('competition_id', $competition->id);
            },
            'competitionEntries.competitionEventEntries' => function ($q) {
                return $q;
            },
            'competitionEntries.competitionEventEntries.competitionEvent' => function ($q) {
                return $q;
            },
        ])->get();

        $members->getCollection()->transform(function ($member) use ($memberModels) {
            return $memberModels->where('MemberID', '=', $member->member_MemberID)->first();
        });

        $members->getCollection()->transform(function (Member $member) use ($competition) {
            return [
                'id' => $member->MemberID,
                'first_name' => $member->MForename,
                'last_name' => $member->MSurname,
                'date_of_birth' => $member->DateOfBirth,
                'age_on_day' => $member->ageAt($competition->age_at_date),
                'competition' => [
                    'id' => $competition->id,
                    'name' => $competition->name,
                ],
                'entries' => $member->competitionEntries,
            ];
        });

        $data = [
            'entrants' => $members,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
        ];

        return Inertia::render('Competitions/Rejections/Index', $data);
    }

    public function refund(Competition $competition, CompetitionEntry $entry, Request $request)
    {
        $this->authorize('refund', $entry);

        /** @var Tenant $tenant */
        $tenant = tenant();

        $request->validate([
            'events' => 'required|array',
            'events.*.event_entry_id' => ['required', 'uuid'],
            'events.*.is_to_refund' => ['required', 'boolean'],
            'events.*.refund_reason' => ['nullable', 'required_if_accepted:events.*.is_to_refund', Rule::enum(CompetitionEntryCancellationReason::class)],
        ]);

        // Check this is a member refund
        if (! $entry->member) {
            throw ValidationException::withMessages([
                'not_member' => 'You are trying to refund a guest entry. That is not possible using this form.',
            ]);
        }

        // Look at submission and refund
        // Redirect back to page

        $events = $request->get('events', []);

        $amountToRefund = 0;
        $competitionEventEntryCollection = collect();

        $payment = null;

        foreach ($events as $event) {
            if (Arr::get($event, 'is_to_refund')) {
                // Get and check event is paid and not refunded etc
                // Then refund

                $eventEntry = $entry->competitionEventEntries()->where('id', $event['event_entry_id'])->first();

                if (! $eventEntry) {
                    continue;
                }

                if (! $eventEntry->paymentLine) {
                    continue;
                } elseif (! $payment) {
                    $payment = $eventEntry->paymentLine->payment;
                }

                if ($eventEntry->paid && ! $eventEntry->refunded) {
                    $refundAmount = $eventEntry->amount - $eventEntry->amount_refunded;
                    $amountToRefund += $refundAmount;
                    $eventEntry->cancellation_reason = $event['refund_reason'];
                    $competitionEventEntryCollection->push($eventEntry);
                }
            }
        }

        if ($competitionEventEntryCollection->isEmpty()) {
            throw ValidationException::withMessages([
                'no_events_to_refund' => 'No events were selected to be refunded.',
            ]);
        }

        if (! $payment) {
            throw ValidationException::withMessages([
                'no_payment' => 'No payment object could be found for this entry.',
            ]);
        }

        DB::beginTransaction();
        try {
            foreach ($competitionEventEntryCollection as $eventEntry) {
                // Save the refund reason
                $eventEntry->save();
            }

            $stripe = new \Stripe\StripeClient([
                'api_key' => config('cashier.secret'),
                'stripe_version' => '2022-11-15',
            ]);

            if ($amountToRefund === 0) {
                throw ValidationException::withMessages([
                    'no_money_to_refund' => 'Events can not be refunded because the total amount to refund is £0.00.',
                ]);
            }

            $refund = $stripe->refunds->create([
                'payment_intent' => $payment->stripe_id,
                'amount' => $amountToRefund,
            ], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);

            if ($refund->status == 'succeeded' || $refund->status == 'pending') {
                /** @var Refund $dbRefund */
                $dbRefund = Refund::firstOrNew([
                    'stripe_id' => $refund->id,
                ], [
                    'payment' => $payment,
                    'amount' => $refund->amount,
                    'status' => $refund->status,
                    'currency' => $refund->currency,
                ]);
                $dbRefund->refunder()->associate(Auth::user());
                $dbRefund->created_at = $refund->created;
                $dbRefund->payment()->associate($payment);
                $dbRefund->save();

                // Refund all paid objects and save changes in the database
                foreach ($competitionEventEntryCollection as $eventEntry) {
                    /** @var CompetitionEventEntry $eventEntry */
                    $refundAmount = $eventEntry->amount - $eventEntry->amount_refunded;

                    $line = $eventEntry->paymentLine;

                    if (! $line) {
                        continue;
                    }

                    $line->amount_refunded = $line->amount_refunded + $refundAmount;
                    if ($line->associated && $line->associated instanceof PaidObject) {
                        $line->associated->handleRefund($refundAmount, $line->amount_refunded);
                    } elseif ($line->associatedUuid && $line->associatedUuid instanceof PaidObject) {
                        $line->associatedUuid->handleRefund($refundAmount, $line->amount_refunded);
                    }
                    $line->save();

                    $dbRefund->lines()->attach($line, [
                        'amount' => $refundAmount,
                    ]);
                }

                $payment->amount_refunded = $payment->amount_refunded + $refund->amount;
                $payment->save();

                $refundFormatted = Money::formatCurrency($refund->amount, $refund->currency);
                $totalRefundedFormatted = Money::formatCurrency($payment->amount_refunded, $refund->currency);
                if ($payment->paymentMethod?->description) {
                    $request->session()->flash('success', "We've refunded {$refundFormatted} to the original payment method ({$payment->paymentMethod->description}). The total amount refunded is {$totalRefundedFormatted}.");
                } else {
                    $request->session()->flash('success', "We've refunded {$refundFormatted} to the original payment method. The total amount refunded is {$totalRefundedFormatted}.");
                }

                try {
                    if ($payment->user) {
                        Mail::to($payment->user)->send(new PaymentRefunded($payment, $refund->amount));
                    }
                } catch (\Exception $e) {
                    // Need to catch the error, but if the email fails, that must not stop the data being committed
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error
            // Flash error
            // Redirect back
            report($e);
            $request->session()->flash('error', 'We were unable to refund the payment. Please try again or contact support.');
        }

        return redirect()->back();
    }
}
