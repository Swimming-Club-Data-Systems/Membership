<?php

namespace App\Providers;

use App\Models\Central\Tenant;
use App\Models\PassportClient;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Cashier\Cashier;
use Laravel\Passport\Passport;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Symfony\Component\HttpFoundation\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Passport::ignoreRoutes();
        Passport::ignoreMigrations();
        Passport::hashClientSecrets();

        \Stripe\Stripe::setApiKey(config('cashier.secret'));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        BelongsToTenant::$tenantIdColumn = 'Tenant';

        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        Request::setTrustedProxies(
            ['REMOTE_ADDR'],
            Request::HEADER_X_FORWARDED_FOR
        );

        Password::defaults(function () {
            $rule = Password::min(8);

            return ! $this->app->isProduction()
                ? $rule->mixedCase()->numbers()->uncompromised()
                : $rule;
        });

        Cashier::useCustomerModel(Tenant::class);
        Cashier::calculateTaxes();

        \Locale::setDefault('en_GB');

        Passport::useClientModel(PassportClient::class);
    }
}
