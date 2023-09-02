<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\CompetitionCourse;
use App\Enums\CompetitionMode;
use App\Enums\CompetitionStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionGuestEntryHeader;
use App\Models\Tenant\CompetitionSession;
use App\Models\Tenant\User;
use App\Models\Tenant\Venue;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

class CompetitionController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        if ($request->user()?->can('create', Competition::class)) {
            if ($request->query('query')) {
                $competitions = Competition::search($request->query('query'))
                    ->where('Tenant', tenant('ID'))
                    ->paginate(config('app.per_page'));
            } else {
                $competitions = Competition::orderBy('gala_date', 'desc')
                    ->paginate(config('app.per_page'));
            }
        } elseif ($request->user()) {
            if ($request->query('query')) {
                $competitions = Competition::search($request->query('query'))
                    ->where('status', CompetitionStatus::PUBLISHED)
                    ->where('Tenant', tenant('ID'))
                    ->paginate(config('app.per_page'));
            } else {
                $competitions = Competition::where('status', '!=', CompetitionStatus::DRAFT)
                    ->orderBy('gala_date', 'desc')
                    ->paginate(config('app.per_page'));
            }
        } else {
            if ($request->query('query')) {
                $competitions = Competition::search($request->query('query'))
                    ->where('public', true)
                    ->where('status', CompetitionStatus::PUBLISHED)
                    ->where('Tenant', tenant('ID'))
                    ->paginate(config('app.per_page'));
            } else {
                $competitions = Competition::where('public', true)
                    ->where('status', '!=', CompetitionStatus::DRAFT)
                    ->orderBy('gala_date', 'desc')
                    ->paginate(config('app.per_page'));
            }
        }

        $competitions->getCollection()->transform(function (Competition $item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'pool_course' => $item->pool_course,
                'venue' => [
                    'id' => $item->venue->id,
                    'name' => $item->venue->name,
                    'formatted_address' => $item->venue->formatted_address,
                ],
                'sessions' => $item->sessions()->get()->map(function (CompetitionSession $item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'start_time' => $item->start_time,
                        'end_time' => $item->end_time,
                    ];
                }),
            ];
        });

        return Inertia::render('Competitions/Index', [
            'competitions' => $competitions->onEachSide(3),
            'can_create' => $request->user()?->can('create', Competition::class),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function new(): \Inertia\Response
    {
        $this->authorize('create', Competition::class);

        return Inertia::render('Competitions/New', [
            'google_maps_api_key' => config('google.maps.clientside'),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function create(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', Competition::class);

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
                'string',
            ],
            'pool_course' => [
                'required',
                new Enum(CompetitionCourse::class),
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
                new Enum(CompetitionMode::class),
            ],
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

    /**
     * @throws AuthorizationException
     */
    public function show(Competition $competition, Request $request): \Inertia\Response
    {
        $this->authorize('view', $competition);

        // Get entries for this user
        //        /** @var User $user */
        //        $user = $request->user();
        //        $competitionEntryHeaders = $user->competitionGuestEntryHeaders()->with(['competitionGuestEntrants']);
        //        foreach ($competitionEntryHeaders as $competitionEntryHeader) {
        //            /** @var CompetitionGuestEntryHeader $competitionEntryHeader */
        //            foreach ($competitionEntryHeader->competitionGuestEntrant as $competitionGuestEntrant) {
        //
        //            }
        //        }

        return Inertia::render('Competitions/Show', [
            'google_maps_api_key' => config('google.maps.clientside'),
            'id' => $competition->id,
            'name' => $competition->name,
            'description' => $competition->description,
            'pool_course' => $competition->pool_course,
            'mode' => $competition->mode,
            'require_times' => $competition->require_times,
            'coach_enters' => $competition->coach_enters,
            'requires_approval' => $competition->requires_approval,
            'status' => $competition->status,
            'public' => $competition->public,
            'processing_fee' => $competition->processing_fee,
            'processing_fee_string' => $competition->processing_fee_string,
            'closing_date' => $competition->closing_date,
            'gala_date' => $competition->gala_date,
            'age_at_date' => $competition->age_at_date,
            'venue' => [
                'id' => $competition->venue->id,
                'name' => $competition->venue->name,
                'place_id' => $competition->venue->place_id,
                'formatted_address' => $competition->venue->formatted_address,
            ],
            'sessions' => $competition->sessions()->get()->toArray(),
            'editable' => $request->user()?->can('update', $competition),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Competition $competition): \Inertia\Response
    {
        $this->authorize('update', $competition);

        return Inertia::render('Competitions/Edit', [
            'form_initial_values' => [
                'name' => $competition->name,
                'description' => $competition->description,
                'pool_course' => $competition->pool_course,
                'require_times' => $competition->require_times,
                'coach_enters' => $competition->coach_enters,
                'requires_approval' => $competition->requires_approval,
                'status' => $competition->status,
                'public' => $competition->public,
                'default_entry_fee_string' => $competition->default_entry_fee_string,
                'processing_fee_string' => $competition->processing_fee_string,
                'closing_date' => $competition->closing_date,
                'age_at_date' => $competition->age_at_date,
                'venue' => $competition->venue->id,
            ],
            'id' => $competition->id,
            'name' => $competition->name,
            'venue' => [
                'id' => $competition->venue->id,
                'name' => $competition->venue->name,
                'place_id' => $competition->venue->place_id,
                'formatted_address' => $competition->venue->formatted_address,
            ],
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Competition $competition, Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $competition);

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
            ],
            'venue' => [
                'required',
                Rule::exists('venues', 'id')->where(function (Builder $query) {
                    return $query->where('Tenant', tenant('id'));
                }),
            ],
            'description' => [
                'string',
            ],
            'pool_course' => [
                'required',
                new Enum(CompetitionCourse::class),
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
            ],
            'age_at_date' => [
                'required',
                'date',
            ],
        ]);

        $competition->fill($validated);
        $competition->venue()->associate($validated['venue']);
        $competition->save();

        // Flash message

        return redirect()->route('competitions.show', $competition);
    }
}
