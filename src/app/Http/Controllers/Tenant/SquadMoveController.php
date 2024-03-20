<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Member;
use App\Models\Tenant\Squad;
use App\Models\Tenant\SquadMove;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SquadMoveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAll', SquadMove::class);

        $moves = SquadMove::orderBy('Date')
            ->with(['member', 'oldSquad', 'newSquad'])
            ->paginate(config('app.per_page'));

        $moves->getCollection()->transform(function (SquadMove $item) {
            return [
                'id' => $item->ID,
                'date' => $item->Date,
                'old_squad' => $item->oldSquad ? [
                    'id' => $item->oldSquad->SquadID,
                    'name' => $item->oldSquad->SquadName,
                    'fee' => $item->oldSquad->fee,
                ] : null,
                'new_squad' => $item->newSquad ? [
                    'id' => $item->newSquad->SquadID,
                    'name' => $item->newSquad->SquadName,
                    'fee' => $item->newSquad->fee,
                ] : null,
                'paying_in_new_squad' => $item->Paying,
                'member' => [
                    'id' => $item->member->MemberID,
                    'name' => $item->member->name,
                ],
            ];
        });

        return Inertia::render('SquadMoves/Index', [
            'moves' => $moves,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', SquadMove::class);

        $request->validate([
            'member' => ['required'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'old_squad' => ['required_without:new_squad'],
            'new_squad' => ['required_without:old_squad'],
            'paying' => ['boolean'],
        ]);

        $squadMove = new SquadMove();

        $squadMove->Date = $request->date('date');
        $squadMove->Paying = $request->boolean('paying');

        $member = Member::findOrFail($request->integer('member'));
        $squadMove->Member = $member->MemberID;

        if ($request->integer('old_squad')) {
            $squad = $squadMove->member->squads->find($request->integer('old_squad'));
            if (! $squad) {
                throw ValidationException::withMessages(['old_squad' => 'Member is not a member of the old squad selected.']);
            }
            $squadMove->Old = $squad->SquadID;
        }
        if ($request->integer('new_squad')) {
            $squad = Squad::findOrFail($request->integer('new_squad'));
            $squadMove->New = $squad->SquadID;
        }

        $squadMove->save();

        if ($squadMove->Date->isToday()) {
            // If the move is today, handle it immediately
            $squadMove->handleMove();
        }

        $request->session()->flash('success', 'Squad move created.');

        return redirect()->back();
    }

    public function update(SquadMove $squadMove, Request $request)
    {
        $this->authorize('update', $squadMove);

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'old_squad' => ['required_without:new_squad'],
            'new_squad' => ['required_without:old_squad'],
            'paying' => ['boolean'],
        ]);

        if ($request->integer('old_squad')) {
            $squad = $squadMove->member->squads->find($request->integer('old_squad'));
            if (! $squad) {
                throw ValidationException::withMessages(['old_squad' => 'Member is not a member of the old squad selected.']);
            }
            $squadMove->Old = $squad->SquadID;
        } else {
            $squadMove->Old = null;
        }
        if ($request->integer('new_squad')) {
            $squad = Squad::findOrFail($request->integer('new_squad'));
            $squadMove->New = $squad->SquadID;
        } else {
            $squadMove->New = null;
        }
        $squadMove->Date = $request->date('date');
        $squadMove->Paying = $request->boolean('paying');

        $squadMove->save();

        if ($squadMove->Date->isToday()) {
            // If the move is today, handle it immediately
            $squadMove->handleMove();
        }

        $request->session()->flash('success', 'Squad move updated.');

        return redirect()->back();
    }

    public function delete(SquadMove $squadMove, Request $request)
    {
        $this->authorize('delete', SquadMove::class);

        $squadMove->delete();

        $request->session()->flash('success', 'Squad move deleted.');

        return redirect()->back();
    }
}
