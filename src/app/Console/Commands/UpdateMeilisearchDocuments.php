<?php

namespace App\Console\Commands;

use App\Models\Tenant\Member;
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
    protected $description = 'Add and remove records from Meilisearch indexes. Only required while V1 is still in operation.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::where('Active', true)->searchable();
        Member::where('Active', true)->searchable();
        User::where('Active', false)->unsearchable();
        Member::where('Active', false)->unsearchable();
        return 0;
    }
}
