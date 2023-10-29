<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\ApplicationFeeAmount;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEventEntry;
use App\Models\Tenant\CompetitionGuestEntrant;
use App\Models\Tenant\CompetitionGuestEntryHeader;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class CompetitionGuestEntryPaymentController extends Controller
{
    private function getGuestEntryHeaderPayment(Competition $competition, CompetitionGuestEntryHeader $header): Payment
    {
        try {
            DB::beginTransaction();

            // Check the header and entries are ready to be paid for

            // Prepare the payment
            $payment = new Payment();
            if ($header->user) {
                $payment->user()->associate($header->user);
            } else {
                $payment->receipt_email = $header->email;
                $payment->customer_name = $header->name;
            }

            $payment->save();

            foreach ($header->competitionGuestEntrants()->get() as $entrant) {
                /** @var CompetitionGuestEntrant $entrant */
                // Get entries

                /** @var CompetitionEntry $entry */
                $entry = CompetitionEntry::where('competition_guest_entrant_id', '=', $entrant->id)->with('competitionEventEntries')->first();

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

                if ($competition->processing_fee > 0 && $entryAmount > 0) {
                    $lineItem = new PaymentLine();
                    $lineItem->unit_amount = $competition->processing_fee;
                    $lineItem->quantity = 1;
                    $lineItem->currency = 'gbp';
                    $lineItem->associatedUuid()->associate($entry);
                    $payment->lines()->save($lineItem);
                }
            }

            $payment->refresh();

            $payment->application_fee_amount = ApplicationFeeAmount::calculateAmount($payment->amount);

            $payment->return_link = route('competitions.enter_as_guest.show', [$competition, $header]);
            $payment->return_link_text = 'Return to entry page';
            $payment->cancel_link = route('competitions.enter_as_guest.show', [$competition, $header]);

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

    /**
     * @throws AuthorizationException
     */
    public function start(Request $request, Competition $competition, CompetitionGuestEntryHeader $header): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $header);

        /** @var Tenant $tenant */
        $tenant = tenant();

        try {
            // Get payment and redirect to checkout

            $payment = $this->getGuestEntryHeaderPayment($competition, $header);

            return Redirect::route('payments.checkout.show', $payment);
        } catch (\Exception $e) {
            $request->session()->flash('error', 'We can\'t take you to the checkout page right now. Please try again later. If the issue persists, please contact '.$tenant->Name.' for help.');

            return Redirect::route('competitions.enter_as_guest.show', [$competition, $header]);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function startJson(Request $request, Competition $competition, CompetitionGuestEntryHeader $header)
    {
        $this->authorize('update', $header);

        /** @var Tenant $tenant */
        $tenant = tenant();

        try {
            // Get payment and redirect to checkout

            $payment = $this->getGuestEntryHeaderPayment($competition, $header);

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
}
