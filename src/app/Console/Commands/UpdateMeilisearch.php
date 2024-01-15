<?php

namespace App\Console\Commands;

use App\Models\Tenant\JournalAccount;
use Illuminate\Console\Command;
use MeiliSearch\Client;

class UpdateMeilisearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Meillisearch settings such as filterableAttributes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        $client->index(config('scout.prefix').'members')->updateFilterableAttributes(['Tenant']);
        $client->index(config('scout.prefix').'users')->updateFilterableAttributes(['Tenant']);
        $client->index(config('scout.prefix').'sms')->updateFilterableAttributes(['Tenant']);
        $client->index(config('scout.prefix').'notifyHistory')->updateFilterableAttributes(['Tenant']);
        $client->index(config('scout.prefix').'journal_accounts')->updateFilterableAttributes(['Tenant']);
        $client->index(config('scout.prefix').'venues')->updateFilterableAttributes(['Tenant']); //, '_geo']);
        $client->index(config('scout.prefix').'competitions')->updateFilterableAttributes(['public', 'status', 'Tenant']);
        $client->index(config('scout.prefix').'squads')->updateFilterableAttributes(['Tenant']);
        $client->index(config('scout.prefix').'products')->updateFilterableAttributes(['Tenant', 'active']);
        JournalAccount::all()->searchable();

        return 0;
    }
}
