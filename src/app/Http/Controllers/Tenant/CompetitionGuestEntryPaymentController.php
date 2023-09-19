<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\ApplicationFeeAmount;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEventEntry;
use App\Models\Tenant\CompetitionGuestEntrant;
use App\Models\Tenant\CompetitionGuestEntryHeader;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use Illuminate\Support\Facades\DB;

class CompetitionGuestEntryPaymentController extends Controller
{
    public function start(Competition $competition, CompetitionGuestEntryHeader $header)
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

                foreach ($entry->competitionEventEntries as $event) {
                    /** @var CompetitionEventEntry $event */
                    $lineItem = new PaymentLine();
                    $lineItem->unit_amount = $event->amount;
                    $lineItem->quantity = 1;
                    $lineItem->currency = 'gbp';
                    $lineItem->associatedUuid()->associate($event);
                    $payment->lines()->save($lineItem);
                }
            }

            $payment->refresh();

            $payment->application_fee_amount = ApplicationFeeAmount::calculateAmount($payment->amount);

            $payment->createStripePaymentIntent();

            $payment->save();

            DB::commit();

            return redirect(route('payments.checkout.show', $payment));

            // Redirect to check out
        } catch (\Exception $e) {
            DB::rollBack();

            ddd($e);
        }
    }
}
