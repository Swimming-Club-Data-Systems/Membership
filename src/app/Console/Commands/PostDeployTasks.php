<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PostDeployTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commands to be run after deployment of a new version';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Artisan::call(UpdateMeilisearch::class);
        return 0;
    }
}
