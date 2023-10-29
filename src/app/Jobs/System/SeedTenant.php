<?php

namespace App\Jobs\System;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Create or update required database objects for a new tenant
 *
 * May also handle creation of other resources
 */
class SeedTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Tenant $tenant,
        public bool $isNew = false
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        CreateDefaultLedgersAndJournals::dispatch($this->tenant);

        if ($this->isNew) {
            // Notify that setup has completed
        }
    }
}
