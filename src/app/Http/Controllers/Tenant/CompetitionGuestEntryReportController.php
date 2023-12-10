<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\CompetitionGuestEntryExport;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CompetitionGuestEntryReportController extends Controller
{
    public function report(Competition $competition, Request $request)
    {
        $this->authorize('viewAny', CompetitionEntry::class);

        return Excel::download(new CompetitionGuestEntryExport($competition), 'entries.xlsx');

        //        $entries = CompetitionEntry::where('competition_id', $competition->id);
        //
        //        if ($request->string('sex') != '') {
        //            $entries = $entries->whereRelation('competitionGuestEntrant', 'sex', '=', $request->string('sex'));
        //        }
        //
        //        $entries = $entries
        //            ->with([
        //                'competitionGuestEntrant',
        //                'competitionEventEntries',
        //                'competitionEventEntries.competitionEvent',
        //            ])
        //            ->get();
        //
        //        return $entries;
    }
}
