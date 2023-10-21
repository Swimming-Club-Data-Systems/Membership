<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Venue;
use App\Rules\ValidPhone;
use Brick\PhoneNumber\PhoneNumberException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VenueController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $this->authorize('viewAny', Venue::class);

        if ($request->query('query')) {
            $venues = Venue::search($request->query('query'))->where('Tenant', tenant('ID'))->paginate(config('app.per_page'));
        } else {
            $venues = Venue::orderBy('Name', 'asc')->paginate(config('app.per_page'));
        }

        $venues->getCollection()->transform(function (Venue $item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'formatted_address' => $item->formatted_address,
            ];
        });

        return Inertia::render('Venues/Index', [
            'venues' => $venues->onEachSide(3),
            'can_create' => $request->user()?->can('create', Venue::class),
        ]);
    }

    public function new(): \Inertia\Response
    {
        $this->authorize('create', Venue::class);

        return Inertia::render('Venues/New', [
            'google_maps_api_key' => config('google.maps.clientside'),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Venue::class);

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
            ],
            'long' => [
                'required',
                'numeric',
            ],
            'lat' => [
                'required',
                'numeric',
            ],
            'phone' => [
                new ValidPhone,
                'max:255',
            ],
            'website' => [
                'url',
                'max:255',
            ],
            'google_maps_url' => [
                'url',
                'max:255',
            ],
            'place_id' => [
                'max:255',
                Rule::unique('venues', 'place_id')
                    ->where(fn ($query) => $query->where('Tenant', tenant('ID'))),
            ],
            'plus_code_global' => [
                'max:255',
            ],
            'plus_code_compound' => [
                'max:255',
            ],
            'vicinity' => [
                'max:255',
            ],
            'formatted_address' => [
                'max:255',
            ],
            'address_components' => [
                'array',
            ],
            'html_attributions' => [
                'array',
            ],
        ]);

        $venue = Venue::create(
            $validated,
        );

        return redirect()->route('venues.show', $venue);
    }

    public function show(Venue $venue): \Inertia\Response
    {
        $this->authorize('view', $venue);

        $phone = null;
        try {
            if ($venue->phone) {
                $phone = [
                    'formatted' => $venue->phone->forCallingFrom(),
                    'url' => $venue->phone->toRfc(),
                ];
            }
        } catch (PhoneNumberException $e) {
            // Could not parse phone number
        }

        return Inertia::render('Venues/Show', [
            'google_maps_api_key' => config('google.maps.clientside'),
            'id' => $venue->id,
            'name' => $venue->name,
            'formatted_address' => $venue->formatted_address,
            'place_id' => $venue->place_id,
            'website' => $venue->website,
            'phone' => $phone,
            'google_maps_url' => $venue->google_maps_url,
        ]);
    }

    public function combobox(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Venue::class);

        $venues = null;
        if ($request->query('query')) {
            $venues = Venue::search($request->query('query'))
                ->where('Tenant', tenant('ID'))
                ->paginate(50);
        }

        $venuesArray = [];

        $selectedVenue = null;
        if ($request->query('id')) {
            /** @var Venue $selectedVenue */
            $selectedVenue = Venue::find($request->query('id'));
            if ($selectedVenue) {
                $venuesArray[] = [
                    'id' => $selectedVenue->id,
                    'name' => $selectedVenue->name,
                ];
            }
        }

        if ($venues) {
            foreach ($venues as $venue) {
                /** @var Venue $venue */
                if ($selectedVenue == null || $selectedVenue->id !== $venue->id) {
                    $venuesArray[] = [
                        'id' => $venue->id,
                        'name' => $venue->name,
                    ];
                }
            }
        }

        $responseData = [
            'data' => $venuesArray,
            'has_more_pages' => $venues && $venues->hasMorePages(),
            'total' => $venues ? $venues->total() : count($venuesArray),
        ];

        return \response()->json($responseData);
    }
}
