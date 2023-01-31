<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MoveMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:move';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform scheduled squad moves to move members to their new squads';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \App\Jobs\MoveMembers::dispatchSync();
        return Command::SUCCESS;
    }
}
