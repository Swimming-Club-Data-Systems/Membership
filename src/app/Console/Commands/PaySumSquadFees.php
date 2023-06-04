<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

class PaySumSquadFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant_payments:sum_squad_fees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sums up squad and extra fee payments for today\'s tenants';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = today()->day;

        $tenants = Tenant::where('data->use_payments_v2', true)->where('data->squad_fee_calculation_date', $date)->get();

        foreach ($tenants as $tenant) {
            /** @var Tenant $tenant */
            $tenant->run(function () use ($tenant) {
                // Calculate for this tenant
                \App\Jobs\PaySumSquadFees::dispatchSync($tenant);
            });
        }

        return Command::SUCCESS;
    }
}
