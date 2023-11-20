<?php

namespace App\Jobs;

use App\Models\Central\Tenant;
use App\Models\Tenant\SquadMove;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;

class MoveMembers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (Tenant::all() as $tenant) {
            /** @var Tenant $tenant */
            $tenant->run(function () use ($tenant) {
                $moves = SquadMove::where('Date', '<=', Date::now())->whereHas('member', function (Builder $query) use ($tenant) {
                    $query->where('Tenant', '=', $tenant->id);
                })->with(['member', 'oldSquad', 'newSquad'])->get();

                foreach ($moves as $move) {
                    /** @var SquadMove $move */
                    if ($move->oldSquad) {
                        $move->member->squads()->detach($move->oldSquad);
                    }

                    if ($move->newSquad) {
                        $move->member->squads()->attach($move->newSquad, [
                            'Paying' => $move->Paying,
                        ]);
                    }

                    // Do not trigger events
                    $move->deleteQuietly();
                }
            });
        }
    }
}
