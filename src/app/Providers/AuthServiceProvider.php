<?php

namespace App\Providers;

use App\Models\Tenant\Passport\Client;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

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
        \App\Models\Tenant\Member::class => \App\Policies\Tenant\MemberPolicy::class,
        \App\Models\Tenant\User::class => \App\Policies\Tenant\UserPolicy::class,
        \App\Models\Central\Tenant::class => \App\Policies\Central\TenantPolicy::class,
        \App\Models\Tenant\Sms::class => \App\Policies\Tenant\SmsPolicy::class,
        \App\Models\Tenant\NotifyHistory::class => \App\Policies\Tenant\NotifyHistoryPolicy::class,
        \App\Models\Tenant\LedgerAccount::class => \App\Policies\Tenant\LedgerAccountPolicy::class,
        \App\Models\Tenant\JournalAccount::class => \App\Policies\Tenant\JournalAccountPolicy::class,
        \App\Models\Tenant\CustomerStatement::class => \App\Policies\Tenant\CustomerStatementPolicy::class
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

        Gate::define('manage', function (\App\Models\Central\User $user) {
            return $user->id === 1
                ? Response::allow()
                : Response::denyAsNotFound(404);
        });

        Passport::useClientModel(Client::class);

        Passport::tokensCan([
            'view-user' => 'View basic user information',
        ]);

        Passport::setDefaultScope([
            'view-user',
        ]);

        Gate::define('manage-settings', function (\App\Models\Tenant\User $user) {
            return $user->hasPermission('Admin')
                ? Response::allow()
                : Response::deny('You are not authorised to manage system settings');
        });
    }
}
