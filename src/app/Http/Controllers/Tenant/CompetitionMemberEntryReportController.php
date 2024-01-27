<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\CompetitionMemberEntryExport;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CompetitionMemberEntryReportController extends Controller
{
    public function report(Competition $competition, Request $request)
    {
        $this->authorize('viewAny', CompetitionEntry::class);

        return Excel::download(new CompetitionMemberEntryExport($competition), 'entries.xlsx');
    }
}
