<?php

namespace App\Console\Commands;

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
        return Command::SUCCESS;
    }
}
