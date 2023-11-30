<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Renewal;
use Inertia\Inertia;

class RenewalController extends Controller
{
    public function index()
    {
        $renewals = Renewal::orderBy('end', 'desc')->with(['clubYear', 'ngbYear'])->paginate(config('app.per_page'));

        return Inertia::render('Renewal/Index', [
            'renewals' => $renewals->onEachSide(3),
        ]);
    }

    public function show(Renewal $renewal)
    {
        return Inertia::render('Renewal/Show', [
            'id' => $renewal->id,
            'start' => $renewal->start,
            'end' => $renewal->end,
            'club_year' => $renewal->clubYear,
            'ngb_year' => $renewal->ngbYear,
        ]);
    }

    public function edit(Renewal $renewal)
    {
        return Inertia::render('Renewal/Edit', [
            'id' => $renewal->id,
            'start' => $renewal->start,
            'end' => $renewal->end,
            'club_year' => $renewal->clubYear,
            'ngb_year' => $renewal->ngbYear,

            'form_initial_values' => [
                'start' => $renewal->start,
                'end' => $renewal->end,
            ],
        ]);
    }

    public function update(Renewal $renewal)
    {
        return Inertia::location(route('renewals.show', $renewal));
    }
}
