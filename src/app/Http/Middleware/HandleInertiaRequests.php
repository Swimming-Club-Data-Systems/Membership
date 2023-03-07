<?php

namespace App\Http\Middleware;

use App\Business\Helpers\AppMenu;
use App\Business\Helpers\CentralAppMenu;
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
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $flashBag = $request->session()->get('flash_bag') ?? [];
        return array_merge(parent::share($request), [
            'auth' => function () use ($request) {
                return [
                    'user' => tenant() ? $request->user() : $request->user('central'),
                ];
            },
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'tenant' => function () use ($request) {
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
                        'menu' => AppMenu::asArray($request->user()),
                    ];
                }
                return null;
            },
            'central' => function () use ($request) {
                if (!tenant()) {
                    return [
                        'menu' => CentralAppMenu::asArray($request->user('central')),
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
