<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

class PaySumFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant_payments:sum_fees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sum up all fees and create statements';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = today()->day;

        $tenants = Tenant::where('data->use_payments_v2', true)->where('data->fee_calculation_date', $date)->get();

        foreach ($tenants as $tenant) {
            /** @var Tenant $tenant */
            $tenant->run(function () use ($tenant) {

                \App\Jobs\PaySumFees::dispatchSync($tenant);

            });
        }

        return Command::SUCCESS;
    }
}
