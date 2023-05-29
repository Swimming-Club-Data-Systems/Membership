<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\Venue;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompetitionController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        if ($request->query('query')) {
            $competitions = Competition::search($request->query('query'))->where('Tenant', tenant('ID'))->paginate(config('app.per_page'));
        } else {
            $competitions = Competition::orderBy('name', 'asc')->paginate(config('app.per_page'));
        }

        $competitions->getCollection()->transform(function (Competition $item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
            ];
        });

        return Inertia::render('Competitions/Index', [
            'competitions' => $competitions->onEachSide(3),
        ]);
    }

    public function new(): \Inertia\Response
    {
        return Inertia::render('Competitions/New', [
            'google_maps_api_key' => config('google.maps.clientside')
        ]);
    }

    public function create(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
            ],
        ]);

        $competition = Competition::create(
            $validated,
        );

        return redirect()->route('venues.show', $competition);
    }

    public function show(Competition $competition): \Inertia\Response
    {
        return Inertia::render('Competitions/Show', [
        ]);
    }
}
