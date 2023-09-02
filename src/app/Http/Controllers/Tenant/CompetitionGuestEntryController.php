<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionSession;
use Inertia\Inertia;

class CompetitionGuestEntryController extends Controller
{
    public function index(Competition $competition)
    {
        $entries = CompetitionEntry::where('competition_id', $competition->id)
            ->with(['competitionGuestEntrant', 'competitionEventEntries', 'competitionEventEntries.competitionEvent'])
            ->paginate(config('app.per_page'));

        $data = [
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
            'entries' => $entries->onEachSide(3),
        ];

        //        $entries->getCollection()->transform(function (CompetitionEntry $item) {
        //            return [
        //                'id' => $item->id,
        //                'name' => $item->name,
        //                'pool_course' => $item->pool_course,
        //                'venue' => [
        //                    'id' => $item->venue->id,
        //                    'name' => $item->venue->name,
        //                    'formatted_address' => $item->venue->formatted_address,
        //                ],
        //                'sessions' => $item->sessions()->get()->map(function (CompetitionSession $item) {
        //                    return [
        //                        'id' => $item->id,
        //                        'name' => $item->name,
        //                        'start_time' => $item->start_time,
        //                        'end_time' => $item->end_time,
        //                    ];
        //                }),
        //            ];
        //        });

        return Inertia::render('Competitions/Entries/GuestEntryList', $data);
    }

    public function show(Competition $competition)
    {

    }
}
