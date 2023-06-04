<?php

namespace App\Console\Commands;

use App\Business\Helpers\AppMenu;
use App\Models\Tenant\User;
use Illuminate\Console\Command;

class GetAppMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:menu {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return string
     */
    public function handle()
    {
        $user = User::find($this->argument('user'));

        $this->line(tenancy()->find($user->Tenant)->run(function () use ($user) {
            return json_encode(AppMenu::asArray($user));
        }));
    }
}
