<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;
use MeiliSearch\Endpoints\Indexes;

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
        $members = null;

        if ($request->search) {
            $members = Member::search($request->query('query'))->where('Tenant', tenant('ID'))->paginate(config('app.per_page'));
        } else {
            $members = Member::orderBy('MForename', 'asc')->orderBy('MSurname', 'asc')->paginate(config('app.per_page'));
        }
        return Inertia::render('Members/Index', [
            'members' => $members->onEachSide(3),
        ]);
    }

    public function show(Member $member) {
        return redirect("/v1/members/" . $member->MemberID);
    }

}
