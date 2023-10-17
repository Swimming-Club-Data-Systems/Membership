<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Squad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SquadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAll', Squad::class);

        $squads = null;

        if ($request->query('query')) {
            $squads = Squad::search($request->query('query'))->where('Tenant', tenant('ID'))->paginate(config('app.per_page'));
        } else {
            $squads = Squad::orderBy('SquadFee', 'desc')->orderBy('SquadName', 'asc')->paginate(config('app.per_page'));
        }

        return Inertia::render('Squads/Index', [
            'squads' => $squads->onEachSide(3),
        ]);
    }

    public function show(Squad $squad)
    {
        $this->authorize('view', $squad);

        return Inertia::location('/v1/squads/'.$squad->SquadID);
    }
}
