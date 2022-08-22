<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        $flashBag = $request->session()->get('flash_bag') ?? [];
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'tenant' => function () {
                $tenant = tenant();
                if ($tenant) {
                    return [
                        'name' => $tenant->getOption("CLUB_NAME"),
                        'short_name' => $tenant->getOption("CLUB_CLUB_NAME"),
                        'asa_code' => $tenant->getOption("ASA_CLUB_CODE"),
                        'asa_district' => $tenant->getOption("ASA_DISTRICT"),
                        'asa_county' => $tenant->getOption("ASA_COUNTY"),
                        'website' => $tenant->getOption("CLUB_WEBSITE"),
                        'club_logo_url' => $tenant->getOption("LOGO_DIR") ? getUploadedAssetUrl($tenant->getOption("LOGO_DIR")) : asset('/img/corporate/scds.svg'),
                    ];
                }
                return null;
            },
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'success' => fn () => $request->session()->get('success'),
                ...$flashBag,
            ],
        ]);
    }
}
