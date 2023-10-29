<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;

class CompetitionEntryController extends Controller
{
    public function index(Competition $competition)
    {
        $entries = CompetitionEntry::where('competition_id', $competition->id)->with(['member', 'competitionEventEntries'])->paginate();
    }

    public function show(Competition $competition)
    {

    }
}
