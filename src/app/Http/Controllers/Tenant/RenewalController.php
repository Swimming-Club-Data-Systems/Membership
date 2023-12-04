<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\OnboardingMember;
use App\Models\Tenant\OnboardingSession;
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
        $userStepValues = [];
        $userLocked = [];
        collect(OnboardingSession::stagesOrder())->each(function ($stage) use (&$userStepValues, &$userLocked, $renewal) {
            $userStepValues[$stage] = $renewal->default_stages[$stage]['required'];
            $userLocked[$stage] = $renewal->default_stages[$stage]['completed'] || $renewal->default_stages[$stage]['required_locked'];
        });
        $memberStepValues = [];
        $memberLocked = [];
        collect(OnboardingMember::stagesOrder())->each(function ($stage) use (&$memberStepValues, &$memberLocked, $renewal) {
            $memberStepValues[$stage] = $renewal->default_member_stages[$stage]['required'];
            $memberLocked[$stage] = $renewal->default_member_stages[$stage]['completed'] || $renewal->default_member_stages[$stage]['required_locked'];
        });

        return Inertia::render('Renewal/Edit', [
            'id' => $renewal->id,
            'start' => $renewal->start,
            'end' => $renewal->end,
            'club_year' => $renewal->clubYear,
            'ngb_year' => $renewal->ngbYear,
            'started' => $renewal->started,
            'user_locked' => $userLocked,
            'member_locked' => $memberLocked,

            'form_initial_values' => [
                'start' => $renewal->start,
                'end' => $renewal->end,
                ...$userStepValues,
                ...$memberStepValues,
            ],
        ]);
    }

    public function update(Renewal $renewal)
    {
        return Inertia::location(route('renewals.show', $renewal));
    }
}
