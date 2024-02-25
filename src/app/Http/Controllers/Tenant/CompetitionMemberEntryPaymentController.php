<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\ApplicationFeeAmount;
use App\Business\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEventEntry;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CompetitionMemberEntryPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function viewPayable(Request $request)
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        /** @var User $user */
        $user = $request->user();
        $compEntries = $user->competitionEntries()->where('paid', '=', false)->with(['member', 'competition'])->get();

        // $notByDirectDebit = $currentUser->getUserBooleanOption('GalaDirectDebitOptOut');
        $notByDirectDebit = $user->getOption('GalaDirectDebitOptOut');

        $tenantNotDirectDebit = ! $tenant->getOption('ENABLE_BILLING_SYSTEM');

        return Inertia::render('Competitions/Pay/SelectEntriesToPayFor', [
            'user_not_direct_debit' => $notByDirectDebit,
            'tenant_not_direct_debit' => $tenantNotDirectDebit,
            'entries' => $compEntries->map(function (CompetitionEntry $entry) {
                return [
                    'id' => $entry->id,
                    'member' => [
                        'id' => $entry->member->MemberID,
                        'name' => $entry->member->name,
                    ],
                    'competition' => [
                        'id' => $entry->competition->id,
                        'name' => $entry->competition->name,
                    ],
                    'amount' => $entry->amount,
                    'amount_currency' => Money::formatCurrency($entry->amount),
                ];
            })->toArray(),
            'form_initial_values' => [
                'entries' => $compEntries->map(function (CompetitionEntry $entry) {
                    return [
                        'id' => $entry->id,
                        'paying' => false,
                        'amount' => $entry->amount,
                    ];
                })->toArray(),
            ],
            'stripe_publishable_key' => config('services.stripe.key'),
            'stripe_account' => $tenant->stripeAccount(),
            'currency' => 'gbp',
            'payment_method_types' => ['card', 'link'],
        ]);
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'entries.*.paying' => ['boolean'],
            'entries.*.id' => ['string', 'required', 'uuid'],
        ]);

        $data = $request->get('entries');

        /** @var Tenant $tenant */
        $tenant = tenant();

        /** @var User $user */
        $user = $request->user();
        $compEntries = $user->competitionEntries()->where('paid', '=', false)->with(['member', 'competition'])->get();

        // TODO Change so that foreach $compEntries and check for ['paying'] in submitted data

        try {
            // Get payment and redirect to checkout

            $entries = [];
            foreach ($data as $entry) {
                if ($entry['paying']) {
                    $entries[] = CompetitionEntry::find($entry['id']);
                }
            }

            $payment = $this->getPayment($request->user(), $entries);

            return Redirect::route('payments.checkout.show', $payment);
        } catch (\Exception $e) {
            $request->session()->flash('error', 'We can\'t take you to the checkout page right now. Please try again later. If the issue persists, please contact '.$tenant->Name.' for help.');

            return Redirect::route('competitions.pay');
        }
    }

    public function createPaymentJson(Request $request)
    {
        $request->validate([
            'entries.*.paying' => ['boolean'],
            'entries.*.id' => ['string', 'required', 'uuid'],
        ]);

        $data = $request->get('entries');

        /** @var Tenant $tenant */
        $tenant = tenant();

        /** @var User $user */
        $user = $request->user();
        $compEntries = $user->competitionEntries()->where('paid', '=', false)->with(['member', 'competition'])->get();

        // TODO Change so that foreach $compEntries and check for ['paying'] in submitted data

        try {
            // Get payment and redirect to checkout

            $entries = [];
            foreach ($data as $entry) {
                if ($entry['paying']) {
                    $entries[] = CompetitionEntry::find($entry['id']);
                }
            }

            $payment = $this->getPayment($request->user(), $entries);

            $stripe = new \Stripe\StripeClient(config('cashier.secret'));

            $paymentIntent = $stripe->paymentIntents->retrieve($payment->stripe_id, [], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'return_url' => route('payments.checkout.show', $payment),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    private function getPayment(User $user, array $entries): Payment
    {
        try {
            DB::beginTransaction();

            // Check the header and entries are ready to be paid for

            // Prepare the payment
            $payment = new Payment();
            $payment->user()->associate($user);

            $payment->save();

            foreach ($entries as $entry) {
                /** @var CompetitionEntry $entry */
                $entryAmount = 0;

                foreach ($entry->competitionEventEntries as $event) {
                    /** @var CompetitionEventEntry $event */
                    $lineItem = new PaymentLine();
                    $lineItem->unit_amount = $event->amount;
                    $entryAmount += $event->amount;
                    $lineItem->quantity = 1;
                    $lineItem->currency = 'gbp';
                    $lineItem->associatedUuid()->associate($event);
                    $payment->lines()->save($lineItem);
                }

                if ($entry->competition->processing_fee > 0 && $entryAmount > 0) {
                    $lineItem = new PaymentLine();
                    $lineItem->unit_amount = $entry->competition->processing_fee;
                    $lineItem->quantity = 1;
                    $lineItem->currency = 'gbp';
                    $lineItem->associatedUuid()->associate($entry);
                    $payment->lines()->save($lineItem);
                }
            }

            $payment->refresh();

            $payment->application_fee_amount = ApplicationFeeAmount::calculateAmount($payment->amount);

            $payment->return_link = route('competitions.pay');
            $payment->return_link_text = 'Return to entry page';
            $payment->cancel_link = route('competitions.pay');

            $payment->createStripePaymentIntent(['card', 'link']);

            $payment->save();

            DB::commit();

            return $payment;
        } catch (\Exception $e) {
            // Catch, rollback, report, rethrow
            DB::rollBack();

            report($e);

            throw $e;
        }
    }
}
