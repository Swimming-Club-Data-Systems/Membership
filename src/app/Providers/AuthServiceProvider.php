<?php

namespace App\Providers;

use App\Models\Tenant\NotifyAdditionalEmail;
use App\Models\Tenant\User;
use App\Policies\Tenant\NotifyAdditionalEmailPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use App\Models\Tenant\Passport\Client;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Tenant\NotifyAdditionalEmail::class => \App\Policies\Tenant\NotifyAdditionalEmailPolicy::class,
        \App\Models\Tenant\Auth\UserCredential::class => \App\Policies\Tenant\Auth\UserCredentialPolicy::class,
        \App\Models\Central\Auth\UserCredential::class => \App\Policies\Central\Auth\UserCredentialPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // if (!$this->app->routesAreCached()) {
        //     Passport::routes();
        // }

        Passport::useClientModel(Client::class);

        Passport::tokensCan([
            'view-user' => 'View basic user information',
        ]);

        Passport::setDefaultScope([
            'view-user',
        ]);
    }
}
