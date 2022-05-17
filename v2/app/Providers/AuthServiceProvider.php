<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Passport\Client;
use Laravel\Passport\Passport;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\ScopeSessions;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (!$this->app->routesAreCached()) {
            Passport::routes(function ($router) {
                $router->forAuthorization();
                $router->forAccessTokens();
            }, ['middleware' => [
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                ScopeSessions::class,
            ]]);
        }
        Passport::tokensCan([
            'view-profile' => 'View your profile information',
        ]);
        Passport::setDefaultScope([
            'view-profile',
        ]);

        Passport::loadKeysFrom(base_path(config('passport.key_path')));

        Passport::useClientModel(Client::class);
    }
}
