<?php

namespace App\Console\Commands\System;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

class CreateDefaultLedgersAndJournals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:populateledgers {tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default system ledgers and journals';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenant = Tenant::find($this->argument('tenant'));

        \App\Jobs\System\CreateDefaultLedgersAndJournals::dispatchSync($tenant);

        return Command::SUCCESS;
    }
}
