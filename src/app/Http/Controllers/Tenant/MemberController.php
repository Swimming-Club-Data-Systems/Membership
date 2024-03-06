<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\Sex;
use App\Http\Controllers\Controller;
use App\Models\Tenant\ClubMembershipClass;
use App\Models\Tenant\Member;
use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MemberController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAll', Member::class);

        /** @var User $user */
        $user = Auth::user();

        $members = null;

        if ($request->query('query')) {
            $members = Member::search($request->query('query'))->where('Tenant', tenant('ID'))->query(fn ($query) => $query->with(['squads' => function ($query) {
                $query->orderBy('SquadFee', 'desc')->orderBy('SquadName', 'asc');
            }]))->paginate(config('app.per_page'));
        } else {
            $members = Member::where('Active', 1)->orderBy('MForename', 'asc')->orderBy('MSurname', 'asc')->with(['squads' => function ($query) {
                $query->orderBy('SquadFee', 'desc')->orderBy('SquadName', 'asc');
            }])->paginate(config('app.per_page'));
        }

        return Inertia::render('Members/Index', [
            'members' => $members->onEachSide(3),
            'can_create' => $user->can('create', Member::class),
        ]);
    }

    public function new()
    {
        $this->authorize('create', Member::class);

        // Array of value/name

        $clubMembershipClass = ClubMembershipClass::where('Type', \App\Enums\ClubMembershipClassType::CLUB)->get();
        $ngb = ClubMembershipClass::where('Type', \App\Enums\ClubMembershipClassType::NGB)->get();

        $clubClasses = [];
        $ngbClasses = [];

        foreach ($clubMembershipClass as $class) {
            /** @var ClubMembershipClass $class */
            $clubClasses[] = [
                'value' => $class->ID,
                'name' => $class->Name,
            ];
        }

        foreach ($ngb as $class) {
            /** @var ClubMembershipClass $class */
            $ngbClasses[] = [
                'value' => $class->ID,
                'name' => $class->Name,
            ];
        }

        return Inertia::render('Members/New', [
            'ngb_membership_classes' => $ngbClasses,
            'club_membership_classes' => $clubClasses,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Member::class);

        $request->validate([
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'ngb_reg' => ['max:36'],
            'ngb_category' => ['required', 'uuid', Rule::exists('clubMembershipClasses', 'ID')->where(function (Builder $query) {
                return $query
                    ->where('Type', \App\Enums\ClubMembershipClassType::NGB)
                    ->where('Tenant', tenant('id'));
            })],
            'club_category' => ['required', 'uuid', Rule::exists('clubMembershipClasses', 'ID')->where(function (Builder $query) {
                return $query
                    ->where('Type', \App\Enums\ClubMembershipClassType::CLUB)
                    ->where('Tenant', tenant('id'));
            })],
            'sex' => ['required', Rule::enum(Sex::class)],
            'club_pays_ngb_fees' => ['boolean'],
            'club_pays_club_membership_fees' => ['boolean'],
        ]);

        $member = new Member([
            'MForename' => $request->string('first_name'),
            'MSurname' => $request->string('last_name'),
            'DateOfBirth' => $request->date('date_of_birth'),
            'ASANumber' => $request->string('ngb_reg'),
            'Gender' => $request->enum('sex', Sex::class),
        ]);

        $member->NGBCategory = $request->string('ngb_category');
        $member->ClubCategory = $request->string('club_category');
        $member->ASAPaid = $request->boolean('club_pays_ngb_fees');
        $member->ClubPaid = $request->boolean('club_pays_club_membership_fees');
        $member->save();

        return redirect()->route('members.show', ['member' => $member->MemberID]);
    }

    public function show(Member $member)
    {
        $this->authorize('view', $member);

        return Inertia::location('/v1/members/'.$member->MemberID);
    }

    public function combobox(Request $request): \Illuminate\Http\JsonResponse
    {
        $members = null;
        if ($request->query('query')) {
            $members = Member::search($request->query('query'))
                ->where('Tenant', tenant('ID'))
                ->paginate(50);
        }

        $membersArray = [];

        $selectedMember = null;
        if ($request->query('id')) {
            /** @var Member $selectedMember */
            $selectedMember = Squad::find($request->query('id'));
            if ($selectedMember) {
                $membersArray[] = [
                    'id' => $selectedMember->MemberID,
                    'name' => $selectedMember->name,
                ];
            }
        }

        if ($members) {
            foreach ($members as $member) {
                /** @var Member $member */
                if ($selectedMember == null || $selectedMember->MemberID !== $member->MemberID) {
                    $membersArray[] = [
                        'id' => $member->MemberID,
                        'name' => $member->name,
                    ];
                }
            }
        }

        $responseData = [
            'data' => $membersArray,
            'has_more_pages' => $members && $members->hasMorePages(),
            'total' => $members ? $members->total() : count($membersArray),
        ];

        return \response()->json($responseData);
    }

    public function squads(Member $member)
    {
        $this->authorize('view', $member);

        $data = $member->squads->map(function (Squad $squad) {
            return [
                'value' => $squad->SquadID,
                'name' => $squad->SquadName,
            ];
        });

        return \response()->json($data);
    }
}
