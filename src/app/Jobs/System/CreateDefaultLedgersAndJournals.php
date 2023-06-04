<?php

namespace App\Jobs\System;

use App\Models\Central\Tenant;
use App\Models\Tenant\JournalAccount;
use App\Models\Tenant\LedgerAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * System Job
 * Checks for existence of default system created ledgers and journal accounts
 *
 * If a ledger or journal does not exist, the system will create one automatically
 */
class CreateDefaultLedgersAndJournals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Tenant $tenant
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Run in tenant context
        $this->tenant->run(function () {
            // Check for Ledger existence
            $generalIncomeLedger = LedgerAccount::firstOrCreate([
                'name' => 'Customer Income',
                'type' => 'income',
                'is_system' => true,
            ]);

            $bankFeesLedger = LedgerAccount::firstOrCreate([
                'name' => 'Bank Fees',
                'type' => 'expense',
                'is_system' => true,
            ]);

            // Check for Journal Existence
            $guestIncomeJournal = JournalAccount::firstOrNew([
                'name' => 'Guest Customers',
                'is_system' => true,
            ]);
            $guestIncomeJournal->ledgerAccount()->associate($generalIncomeLedger);
            $guestIncomeJournal->save();

            $stripeFeesJournal = JournalAccount::firstOrNew([
                'name' => 'Stripe Fees',
                'is_system' => true,
            ]);
            $stripeFeesJournal->ledgerAccount()->associate($bankFeesLedger);
            $stripeFeesJournal->save();
        });
    }
}
