<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Member;
use Illuminate\Http\Request;
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
        ]);
    }

    public function show(Member $member)
    {
        $this->authorize('view', $member);

        return Inertia::location('/v1/members/'.$member->MemberID);
    }
}
