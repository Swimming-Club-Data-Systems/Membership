<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Member;
use App\Models\Tenant\Squad;
use App\Models\Tenant\SquadMove;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SquadMoveController extends Controller
{
    public function index(Request $request)
    {
        //        $this->authorize('viewAll', SquadMove::class);

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
        $request->validate([
            'member' => ['required'],
            'date' => ['required', 'date'],
            'old_squad' => ['required_without:new_squad'],
            'new_squad' => ['required_without:old_squad'],
            'paying' => ['boolean'],
        ]);

        $squadMove = new SquadMove();

        $member = Member::findOrFail($request->integer('member'));

        $squadMove->member()->associate($member);
        if ($request->integer('old_squad')) {
            $squadMove->oldSquad()->associate(Squad::findOrFail($request->integer('old_squad')));
        }
        if ($request->integer('new_squad')) {
            $squadMove->newSquad()->associate(Squad::findOrFail($request->integer('new_squad')));
        }
        $squadMove->Date = $request->date('date');
        $squadMove->Paying = $request->boolean('paying');

        $squadMove->save();

        //        $squad->coaches()->attach($user, [
        //            'type' => $type,
        //        ]);
        $request->session()->flash('success', 'Squad move created.');

        return redirect()->back();
    }

    public function update(SquadMove $squadMove, Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'old_squad' => ['required_without:new_squad'],
            'new_squad' => ['required_without:old_squad'],
            'paying' => ['boolean'],
        ]);

        if ($request->integer('old_squad')) {
            $squadMove->oldSquad()->associate(Squad::findOrFail($request->integer('old_squad')));
        } else {
            $squadMove->oldSquad()->disassociate();
        }
        if ($request->integer('new_squad')) {
            $squadMove->newSquad()->associate(Squad::findOrFail($request->integer('new_squad')));
        } else {
            $squadMove->newSquad()->disassociate();
        }
        $squadMove->Date = $request->date('date');
        $squadMove->Paying = $request->boolean('paying');

        $squadMove->save();

        $request->session()->flash('success', 'Squad move updated.');

        return redirect()->back();
    }

    public function delete(SquadMove $squadMove, Request $request)
    {
        $squadMove->delete();

        $request->session()->flash('success', 'Squad move deleted.');

        return redirect()->back();
    }
}
