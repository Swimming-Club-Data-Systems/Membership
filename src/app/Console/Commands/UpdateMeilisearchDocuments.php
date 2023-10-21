<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\Member;
use App\Models\Tenant\NotifyHistory;
use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use Illuminate\Console\Command;

class UpdateMeilisearchDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add and remove records from Meilisearch indexes. Only required while V1 create and update logic is still in operation for a class listed below.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Remove classes as they are migrated to V2
        User::where('Active', true)->searchable();
        Member::where('Active', true)->searchable();
        Tenant::where('Verified', true)->searchable();
        User::where('Active', false)->unsearchable();
        Member::where('Active', false)->unsearchable();
        Tenant::where('Verified', false)->unsearchable();
        NotifyHistory::all()->searchable();
        Squad::all()->searchable();

        return 0;
    }
}
