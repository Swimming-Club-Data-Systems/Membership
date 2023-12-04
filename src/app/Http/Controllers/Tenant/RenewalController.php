<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\MembershipYear;
use App\Models\Tenant\OnboardingMember;
use App\Models\Tenant\OnboardingSession;
use App\Models\Tenant\Renewal;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class RenewalController extends Controller
{
    public function index()
    {
        $renewals = Renewal::orderBy('end', 'desc')->with(['clubYear', 'ngbYear'])->paginate(config('app.per_page'));

        return Inertia::render('Renewal/Index', [
            'renewals' => $renewals->onEachSide(3),
            'can_create' => true,
        ]);
    }

    public function new()
    {
        $userStepValues = [];
        $userFields = [];
        collect(OnboardingSession::stagesOrder())->each(function ($stage) use (&$userStepValues, &$userFields) {
            $stageData = OnboardingSession::getDefaultRenewalStages()[$stage];
            $userStepValues[$stage] = $stageData['required'];
            $userFields[] = [
                'id' => $stage,
                'name' => OnboardingSession::stages()[$stage],
                'locked' => $stageData['required_locked'],
            ];
        });
        $memberStepValues = [];
        $memberFields = [];
        collect(OnboardingMember::stagesOrder())->each(function ($stage) use (&$memberStepValues, &$memberFields) {
            $stageData = OnboardingMember::getDefaultStages()[$stage];
            $memberStepValues[$stage] = $stageData['required'];
            $memberFields[] = [
                'id' => $stage,
                'name' => OnboardingMember::stages()[$stage],
                'locked' => $stageData['required_locked'],
            ];
        });

        $membershipYearSelect = [
            [
                'value' => 'N/A',
                'name' => 'None',
            ],
        ];
        $membershipYears = MembershipYear::orderBy('EndDate', 'desc')->limit(10)->get();

        foreach ($membershipYears as $membershipYear) {
            /** @var MembershipYear $membershipYear */
            $membershipYearSelect[] = [
                'value' => $membershipYear->ID,
                'name' => $membershipYear->Name,
            ];
        }

        return Inertia::render('Renewal/New', [
            'started' => false,
            'user_fields' => $userFields,
            'member_fields' => $memberFields,
            'form_initial_values' => [
                ...$userStepValues,
                ...$memberStepValues,
                'ngb_year' => 'N/A',
                'club_year' => 'N/A',
                'credit_debit' => true,
            ],
            'membership_years' => $membershipYearSelect,
        ]);
    }

    public function create()
    {

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
        $userFields = [];
        collect(OnboardingSession::stagesOrder())->each(function ($stage) use (&$userStepValues, &$userFields, $renewal) {
            $userStepValues[$stage] = $renewal->default_stages[$stage]['required'];
            $userFields[] = [
                'id' => $stage,
                'name' => OnboardingSession::stages()[$stage],
                'locked' => $renewal->default_stages[$stage]['completed'] || $renewal->default_stages[$stage]['required_locked'],
            ];
        });
        $memberStepValues = [];
        $memberFields = [];
        collect(OnboardingMember::stagesOrder())->each(function ($stage) use (&$memberStepValues, &$memberFields, $renewal) {
            $memberStepValues[$stage] = $renewal->default_member_stages[$stage]['required'];
            $memberFields[] = [
                'id' => $stage,
                'name' => OnboardingMember::stages()[$stage],
                'locked' => $renewal->default_member_stages[$stage]['completed'] || $renewal->default_member_stages[$stage]['required_locked'],
            ];
        });

        $ngbBillDate = Arr::get($renewal->metadata, 'custom_direct_debit_bill_dates.ngb');
        $clubBillDate = Arr::get($renewal->metadata, 'custom_direct_debit_bill_dates.club');

        return Inertia::render('Renewal/Edit', [
            'id' => $renewal->id,
            'start' => $renewal->start,
            'end' => $renewal->end,
            'club_year' => $renewal->clubYear,
            'ngb_year' => $renewal->ngbYear,
            'started' => $renewal->started,
            'user_fields' => $userFields,
            'member_fields' => $memberFields,

            'form_initial_values' => [
                'start_date' => $renewal->start,
                'end_date' => $renewal->end,
                ...$userStepValues,
                ...$memberStepValues,
                'use_custom_billing_dates' => $ngbBillDate || $clubBillDate,
                'dd_club_bills_date' => $clubBillDate,
                'dd_ngb_bills_date' => $ngbBillDate,
            ],
        ]);
    }

    public function update(Renewal $renewal, Request $request)
    {
        $rules = [
            'start_date' => ['date', 'required'],
            'end_date' => ['date', 'required', 'after:start_date'],
            'dd_ngb_bills_date' => ['date', 'required_if_accepted:use_custom_billing_dates'],
            'dd_club_bills_date' => ['date', 'required_if_accepted:use_custom_billing_dates'],
        ];

        $stages = collect(OnboardingSession::stagesOrder());
        $memberStages = collect(OnboardingMember::stagesOrder());

        $stages->each(function ($stage) use (&$rules) {
            $rules[$stage] = [
                'boolean',
                'required',
            ];
        });

        $memberStages->each(function ($stage) use (&$rules) {
            $rules[$stage] = [
                'boolean',
                'required',
            ];
        });

        $request->validate($rules);

        $renewal->start = $request->date('start_date');
        $renewal->end = $request->date('end_date');

        if (! $renewal->started) {
            $stages->each(function ($stage) use ($renewal, $request) {
                $renewal->default_member_stages[$stage]['required'] = $request->boolean($stage);
            });

            $memberStages->each(function ($stage) use ($renewal, $request) {
                $renewal->default_member_stages[$stage]['required'] = $request->boolean($stage);
            });
        }

        $data = [
            'ngb' => null,
            'club' => null,
        ];
        if ($request->boolean('use_custom_billing_dates')) {
            $data['ngb'] = $request->date('dd_ngb_bills_date');
            $data['club'] = $request->date('dd_club_bills_date');
        }
        $renewal->metadata['custom_direct_debit_bill_dates'] = $data;

        $renewal->save();

        return Inertia::location(route('renewals.show', $renewal));
    }
}
