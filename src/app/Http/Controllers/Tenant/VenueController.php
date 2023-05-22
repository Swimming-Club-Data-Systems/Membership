<?php

namespace App\Http\Controllers\Tenant;

use App\Business\CollectionTransforms\NotifyHistoryTransform;
use App\Http\Controllers\Controller;
use App\Models\Tenant\NotifyHistory;
use App\Models\Tenant\Venue;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VenueController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
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
        ]);
    }

    public function new(): \Inertia\Response
    {
        return Inertia::render('Venues/New', [
            'google_maps_api_key' => config('google.maps.clientside')
        ]);
    }

    public function create(Request $request)
    {
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
        return Inertia::render('Venues/Show', [
            'google_maps_api_key' => config('google.maps.clientside'),
            'id' => $venue->id,
            'name' => $venue->name,
            'formatted_address' => $venue->formatted_address,
            'place_id' => $venue->place_id,
        ]);
    }
}
