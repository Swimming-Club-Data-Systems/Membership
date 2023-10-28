<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Member;
use App\Models\Tenant\Post;
use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

        /** @var User $user */
        $user = $request->user();

        $squads = null;

        if ($request->query('query')) {
            $squads = Squad::search($request->query('query'))->where('Tenant', tenant('ID'))->paginate(config('app.per_page'));
        } else {
            $squads = Squad::orderBy('SquadFee', 'desc')->orderBy('SquadName', 'asc')->paginate(config('app.per_page'));
        }

        return Inertia::render('Squads/Index', [
            'squads' => $squads->onEachSide(3),
            'can_create' => $user->can('create', Squad::class),
        ]);
    }

    public function new(Request $request)
    {
        $this->authorize('create', Squad::class);

        return Inertia::render('Squads/New', [
            'codes_of_conduct' => Post::where('Type', PostType::CONDUCT_CODE)->get()->map(function ($post) {
                return [
                    'value' => $post->ID,
                    'name' => $post->Title,
                ];
            }),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Squad::class);

        $squad = new Squad();

        $request->validate([
            'name' => ['string', 'required', 'max:255'],
            'monthly_fee' => ['decimal:0,2', 'required', 'min:0'],
            'timetable' => ['string', 'nullable', 'max:100', 'url'],
            'code_of_conduct' => [
                'nullable',
                Rule::exists('posts', 'ID')->where(function (Builder $query) {
                    return $query->where('Tenant', tenant('id'));
                }),
            ],
        ]);

        $squad->SquadName = $request->string('name');
        $squad->SquadFee = $request->float('monthly_fee');
        $squad->SquadTimetable = $request->string('timetable');
        $squad->SquadCoC = $request->string('code_of_conduct');

        $squad->save();

        $request->session()->flash('success', $squad->SquadName.' successfully created.');

        return redirect()->route('squads.show', $squad);
    }

    public function show(Request $request, Squad $squad)
    {
        $this->authorize('view', $squad);

        /** @var User $user */
        $user = $request->user();

        $canEdit = $user->can('update', $squad);
        $canViewMembers = $user->can('viewAll', Member::class);

        $members = [];
        $coaches = [];

        $twoWeeksAgo = (new Carbon())->subWeeks(2);

        if ($canViewMembers) {
            foreach ($squad
                ->members()
                ->orderBy('MForename')
                ->orderBy('MSurname')
                ->with('memberMedical')
                ->get() as $member) {
                $members[] = [
                    'id' => $member->MemberID,
                    'name' => $member->name,
                    'date_of_birth' => $member->DateOfBirth,
                    'pronouns' => $member->pronouns,
                    'age' => $member->age(),
                    'medical' => $member->memberMedical,
                    'medical_consent_withheld' => $member->memberMedical?->WithholdConsent ?? false,
                    'medical_conditions_recently_updated' => $member->memberMedical?->updated_at > $twoWeeksAgo,
                ];
            }
        }

        foreach ($squad
            ->coaches()
            ->orderByPivot('Type')
            ->orderBy('Forename')
            ->orderBy('Surname')
            ->get() as $coach) {
            $coaches[] = [
                'id' => $coach->UserID,
                'name' => $coach->name,
                'type' => Str::title($coach->pivot->Type),
            ];
        }

        $timetable = Str::length($squad->SquadTimetable) > 0 ? $squad->SquadTimetable : $request->schemeAndHttpHost().'/v1/sessions?squad='.urlencode($squad->SquadID);

        return Inertia::render('Squads/Show', [
            'id' => $squad->SquadID,
            'name' => $squad->SquadName,
            'timetable_url' => $timetable,
            'timetable_url_display' => trim(str_replace(['https://www.', 'http://www.', 'http://', 'https://'], '', $timetable), '/'),
            'monthly_fee_formatted' => Money::formatCurrency($squad->fee, 'gbp'),
            'members' => $canViewMembers ? $members : null,
            'coaches' => $coaches,
            'editable' => $canEdit,
        ]);
    }

    public function edit(Request $request, Squad $squad)
    {
        $this->authorize('update', $squad);

        $coc = (int) $squad->SquadCoC;

        return Inertia::render('Squads/Edit', [
            'id' => $squad->SquadID,
            'name' => $squad->SquadName,
            'form_initial_values' => [
                'name' => $squad->SquadName,
                'monthly_fee' => Money::formatDecimal($squad->fee),
                'timetable' => $squad->SquadTimetable,
                'code_of_conduct' => $coc == 0 ? null : $coc,
            ],
            'codes_of_conduct' => Post::where('Type', PostType::CONDUCT_CODE)->get()->map(function ($post) {
                return [
                    'value' => $post->ID,
                    'name' => $post->Title,
                ];
            }),
        ]);
    }

    public function update(Request $request, Squad $squad)
    {
        $this->authorize('update', $squad);

        $request->validate([
            'name' => ['string', 'required', 'max:255'],
            'monthly_fee' => ['decimal:0,2', 'required', 'min:0'],
            'timetable' => ['string', 'nullable', 'max:100', 'url'],
            'code_of_conduct' => [
                'nullable',
                Rule::exists('posts', 'ID')->where(function (Builder $query) {
                    return $query->where('Tenant', tenant('id'));
                }),
            ],
        ]);

        $squad->SquadName = $request->string('name');
        $squad->SquadFee = $request->float('monthly_fee');
        $squad->SquadTimetable = $request->string('timetable');
        $squad->SquadCoC = $request->string('code_of_conduct');

        $squad->save();

        $request->session()->flash('success', 'Changes saved successfully.');

        return redirect()->route('squads.show', $squad);
    }

    public function delete(Request $request, Squad $squad)
    {
        $this->authorize('delete', $squad);

        $squad->delete();

        $request->session()->flash('success', $squad->SquadName.' has been successfully deleted.');

        return redirect()->route('squads.index');
    }
}
