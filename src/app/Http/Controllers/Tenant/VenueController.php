<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Venue;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VenueController extends Controller
{
    public function index() {

    }

    public function new() {
        return Inertia::render('Venues/New', [
            'google_maps_api_key' => config('google.maps.clientside')
        ]);
    }

    public function create(Request $request) {
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

        return $venue;
    }
}
