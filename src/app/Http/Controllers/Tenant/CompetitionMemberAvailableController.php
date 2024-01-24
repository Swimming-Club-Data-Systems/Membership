<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompetitionMemberAvailableController extends Controller
{
    public function index(Competition $competition, Request $request)
    {
        $members = DB::table('competition_session_member')
            ->join('competition_sessions', 'competition_session_member.competition_session_id', '=', 'competition_sessions.id')
            ->join('members', 'competition_session_member.member_MemberID', '=', 'members.MemberID')
            ->where('competition_sessions.competition_id', '=', $competition->id)
            ->select('member_MemberID')
            ->orderBy('members.MSurname', 'asc')
            ->orderBy('members.MForename', 'asc')
            ->distinct()
            ->paginate();

        $ids = collect($members->items())->map(fn ($item) => $item->member_MemberID);

        //        $memberModels = Member::findMany($ids);

        $memberModels = Member::whereIn('MemberID', $ids)->with(['competitionEntries' => function ($q) use ($competition) {
            $q->where('competition_id', $competition->id);
        }])->get();

        $members->getCollection()->transform(function ($member) use ($memberModels) {
            return $memberModels->where('MemberID', '=', $member->member_MemberID)->first();
        });

        $members->getCollection()->transform(function (Member $member) use ($competition) {
            return [
                'id' => $member->MemberID,
                'first_name' => $member->MForename,
                'last_name' => $member->MSurname,
                'date_of_birth' => $member->DateOfBirth,
                'age_on_day' => $member->ageAt($competition->age_at_date),
                'entries' => $member->competitionEntries,
            ];
        });

        return Inertia::render('Competitions/Entries/AvailableMembers', [
            'members' => $members,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
        ]);
    }
}
