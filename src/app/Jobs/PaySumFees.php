<?php

namespace App\Jobs;

use App\Models\Central\Tenant;
use App\Models\Tenant\CustomerStatement;
use App\Models\Tenant\User;
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
            $users = User::where('Active', true)->get();
            foreach ($users as $user) {
                /** @var User $user */

                // Create statement
                $statement = CustomerStatement::createStatement($user);

                // If auto direct debit enabled and user has DD
//                if (true) {
//                    // Schedule a payment on their payment date
//                    $statement->closing_balance;
//                }

                // Create a statement for the user including all payments in the current period
                // We record the system's last PaySumFees run date
                // We set the PaySumFees date when Payments V2 is turned on as that date
                // The "statement" is a "statement" model with a linker table pointing at the transactions

                /**
                 * A statement needs the following
                 *
                 * id
                 * start_date
                 * end_date
                 * user
                 * opening_balance $journal->getBalanceOn(date)
                 * credits $journal->creditedBetween(start, end)
                 * debits $journal->debitedBetween(start, end)
                 * closing_balance $journal->getBalanceOn(date) - this is the balance the schedule for payment
                 */

                $balance = $journal->getBalance();

                if ($balance > 100) {
                    // Balance greater than Stripe minimum, schedule a payment for it
                }
            }
        });
    }
}
