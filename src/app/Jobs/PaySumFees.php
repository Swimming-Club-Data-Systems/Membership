<?php

namespace App\Jobs;

use App\Models\Central\Tenant;
use App\Models\Tenant\BalanceTopUp;
use App\Models\Tenant\CustomerStatement;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PaySumFees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Tenant $tenant
    )
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->tenant->run(function () {

            // Tenant Charge date
            $chargeDate = Carbon::now()->startOfDay();
            $dayOfMonth = $this->tenant->billing_date ?? 1;

            // If the selected billing date is higher than the last day in the month,
            // set it to the last day of the month
            if ($dayOfMonth > $chargeDate->daysInMonth) {
                $dayOfMonth = $chargeDate->daysInMonth;
            }

            // Set the day of month on the charge date
            $chargeDate->day = $dayOfMonth;

            // If the charge date is less than today
            if ($chargeDate < Carbon::now()->startOfDay()) {
                // Advance the month by one
                $chargeDate->addMonth();
            }

            $users = User::where('Active', true)->get();
            foreach ($users as $user) {
                /** @var User $user */

                // Copy Charge Date in case we customise it for this user
                $scheduledForDate = $chargeDate->copy();

                // TODO Calculate custom scheduled_for date. Niche edge case.

                // Create statement
                $statement = CustomerStatement::createStatement($user);

                // Get a direct debit payment method for this user
                $paymentMethod = $user->preferredDirectDebit();

                if ($paymentMethod && $statement->closing_balance <= -100) {
                    // Create a balance top up if g.t.e. £1 and user has a usable payment method
                    $balanceTopUp = new BalanceTopUp();
                    $balanceTopUp->user()->associate($user);
                    $balanceTopUp->amount = abs($statement->closing_balance);
                    $balanceTopUp->scheduled_for = $scheduledForDate;
                    $balanceTopUp->paymentMethod()->associate($paymentMethod);
                    $balanceTopUp->save();
                }

                // Generate statement emails and documents
            }
        });
    }
}
