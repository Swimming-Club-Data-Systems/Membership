<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;
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
        $tenantData = [];

        if (tenant()) {
            $tenantData = [

            ];
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => function () {
                return (new Ziggy)->toArray();
            },
            'app' => [
                'environment' => App::environment(),
                'debug' => env('APP_DEBUG', false),
                'locale' => App::getLocale(),
                'timezone' => 'Europe/London',
                'isLocal' => App::isLocal(),
                'isProduction' => App::isProduction(),
            ],
        ], $tenantData);
    }
}
