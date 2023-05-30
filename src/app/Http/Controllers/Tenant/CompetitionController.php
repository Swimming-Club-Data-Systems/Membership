<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\CompetitionCourse;
use App\Enums\CompetitionMode;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\Venue;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
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
            'venue_select' => [
                'required',
                Rule::exists('venues', 'id')->where(function (Builder $query) {
                    return $query->where('Tenant', tenant('id'));
                }),
            ],
            'description' => [
                'string'
            ],
            'pool_course' => [
                'required',
                new Enum(CompetitionCourse::class)
            ],
            'require_times' => [
                'required',
                'boolean',
            ],
            'coach_enters' => [
                'required',
                'boolean',
            ],
            'requires_approval' => [
                'required',
                'boolean',
            ],
            'public' => [
                'required',
                'boolean',
            ],
            'default_entry_fee' => [
                'decimal:0,2',
                'min:0',
            ],
            'processing_fee' => [
                'decimal:0,2',
                'min:0',
            ],
            'closing_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'age_at_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'gala_date' => [
                'date',
                'after_or_equal:today',
            ],
            'setup_type' => [
                'required',
                new Enum(CompetitionMode::class)
            ]
        ]);

        $competition = new Competition();
        $competition->name = $request->string('name');
        $competition->description = $request->string('description', '');
        $competition->venue()->associate(Venue::find($request->integer('venue_select')));
        $competition->pool_course = $request->enum('pool_course', CompetitionCourse::class);
        $competition->require_times = $request->boolean('require_times');
        $competition->coach_enters = $request->boolean('coach_enters');
        $competition->requires_approval = $request->boolean('requires_approval');
        $competition->public = $request->boolean('public');
        $competition->default_entry_fee_string = $request->string('default_entry_fee');
        $competition->processing_fee_string = $request->string('processing_fee');
        $competition->closing_date = $request->date('closing_date');
        $competition->age_at_date = $request->date('age_at_date');
        $competition->mode = $request->enum('setup_type', CompetitionMode::class);
        if ($competition->mode == CompetitionMode::BASIC) {
            $competition->gala_date = $request->date('gala_date');
        } else {
            $competition->gala_date = $competition->age_at_date;
        }

        $competition->save();

        return redirect()->route('competitions.show', $competition);
    }

    public function show(Competition $competition): \Inertia\Response
    {
        return Inertia::render('Competitions/Show', [
        ]);
    }
}
