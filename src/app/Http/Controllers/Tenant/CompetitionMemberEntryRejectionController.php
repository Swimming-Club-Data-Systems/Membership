<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CompetitionMemberEntryRejectionController extends Controller
{
    public function index(Competition $competition)
    {
        $this->authorize('viewAny', CompetitionEntry::class);

        $members = DB::table('competition_entries')
            ->join('members', 'competition_entries.member_MemberID', '=', 'members.MemberID')
            ->where('competition_entries.competition_id', '=', $competition->id)
            ->select('member_MemberID')
            ->orderBy('members.MSurname', 'asc')
            ->orderBy('members.MForename', 'asc')
            ->distinct()
            ->paginate();

        $ids = collect($members->items())->map(fn ($item) => $item->member_MemberID);

        //        $memberModels = Member::findMany($ids);

        $memberModels = Member::whereIn('MemberID', $ids)->with([
            'competitionEntries' => function ($q) use ($competition) {
                $q->where('competition_id', $competition->id);
            },
            'competitionEntries.competitionEventEntries' => function ($q) {
                return $q;
            },
            'competitionEntries.competitionEventEntries.competitionEvent' => function ($q) {
                return $q;
            },
        ])->get();

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
                'competition' => [
                    'id' => $competition->id,
                    'name' => $competition->name,
                ],
                'entries' => $member->competitionEntries,
            ];
        });

        $data = [
            'entrants' => $members,
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
        ];

        return Inertia::render('Competitions/Rejections/Index', $data);
    }

    public function refund(Competition $competition, CompetitionEntry $entry, Request $request)
    {
        $this->authorize('refund', $entry);

        // Check this is a member refund
        if (! $entry->member) {
            throw ValidationException::withMessages([
                'not_member' => 'You are trying to refund a guest entry. That is not possible using this form.',
            ]);
        }

        // Look at submission and refund
        // Redirect back to page

        $events = $request->get('events', []);

        foreach ($events as $event) {
            if (Arr::get($event, 'is_to_refund')) {
                // Get and check event is paid and not refunded etc
                // Then refund
            }
        }

        dd($events);

        // Loop over rejections and run Stripe call for refund

        $request->session()->flash('success', $entry->member->name.'\'s entry has been partially/fully refunded.');

        return redirect()->back();
    }
}
