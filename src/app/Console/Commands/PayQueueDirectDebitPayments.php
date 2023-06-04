<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\BalanceTopUp;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PayQueueDirectDebitPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant_payments:queue_direct_debit_payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queues Direct Debit payments for users who pay today';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants = Tenant::where('data->use_payments_v2', true)->get();

        $now = Carbon::now()->endOfDay();

        foreach ($tenants as $tenant) {
            /** @var Tenant $tenant */
            $tenant->run(function () use ($now) {

                // Get BalanceTopUps where the charge date is <= now
                $topups = BalanceTopUp::where('status', 'pending')
                    ->where('scheduled_for', '<=', $now)
                    ->with('paymentMethod')
                    ->get();

                foreach ($topups as $topup) {
                    /** @var BalanceTopUp $topup */
                    \App\Jobs\PayChargeFees::dispatch($topup);
                }

            });
        }

        return Command::SUCCESS;
    }
}
