<?php

namespace App\Http\Controllers\Central;

use App\Business\OAuthProviders\Stripe;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $tenants = null;

        if ($request->query('query')) {
            $tenants = Tenant::search($request->query('query'))->query(fn($query) => $query->with(['tenantOptions' => function ($query) {
                $query->where('Option', 'LOGO_DIR');
            }]))->paginate(config('app.per_page'));
        } else {
            $tenants = Tenant::where('Verified', true)->orderBy('Name', 'asc')->with(['tenantOptions' => function ($query) {
                $query->where('Option', 'LOGO_DIR');
            }])->paginate(config('app.per_page'));
        }
        return Inertia::render('Central/Tenants/Index', [
            'tenants' => $tenants->onEachSide(3),
        ]);
    }

    public function show(Tenant $tenant, Request $request)
    {
        return Inertia::render('Central/Tenants/Show', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'form_initial_values' => [
                'name' => $tenant->Name,
                'code' => $tenant->Code,
                'website' => $tenant->Website,
                'email' => $tenant->Email,
                'verified' => $tenant->Verified,
                'domain' => $tenant->Domain,
            ]
        ]);
    }

    public function save(Tenant $tenant, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:128'],
            'code' => ['required', 'size:4'],
            'email' => ['required', 'email:rfc,dns', 'max:256'],
            'website' => ['required', 'url', 'max:256'],
            'verified' => ['required', 'boolean'],
            'domain' => ['required', 'max:256'],
        ]);

        $tenant->Name = $request->input('name');
        $tenant->Code = Str::upper($request->input('code'));
        $tenant->Email = $request->input('email');
        $tenant->Website = $request->input('website');
        $tenant->Verified = $request->input('verified');
        $tenant->Domain = $request->input('domain');
        $tenant->save();

        $request->session()->flash('success', "We've saved your changes to {$tenant->Name}.");

        return Redirect::route('central.tenants.show', $tenant);
    }

    public function stripe(Tenant $tenant, Request $request)
    {
        return Inertia::render('Central/Tenants/Stripe', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'stripe_account' => $tenant->getOption('STRIPE_ACCOUNT_ID'),
        ]);
    }

    public function stripeOAuthStart(Tenant $tenant, Request $request)
    {
        // You should store your client ID and secret in environment variables rather than
        // committing them with your code

        abort_unless(config('services.stripe.client_id') && config('services.stripe.key'), 503, 'Stripe has not been enabled on this instance');

        $provider = new Stripe([
            'clientId' => config('services.stripe.client_id'),
            'clientSecret' => config('cashier.secret'),
            'redirectUri' => route('central.tenants.setup_stripe_redirect'),
        ]);

        $authUrl = $provider->getAuthorizationUrl();
        $state = $provider->getState();

        $request->session()->put('stripe_oauth_tenant', $tenant->ID);
        $request->session()->put('stripe_oauth', $state);

        return Inertia::location($authUrl);
    }

    public function stripeOAuthRedirect(Request $request)
    {
        abort_unless(config('services.stripe.client_id') && config('services.stripe.key'), 503, 'Stripe has not been enabled on this instance');

        $tenant = null;

        if ($request->session()->exists('stripe_oauth_tenant')) {
            /** @var Tenant $tenant */
            $tenant = Tenant::findOrFail($request->session()->pull('stripe_oauth_tenant'));
        }

        if ($request->input('code') && $request->input('state') === $request->session()->pull('stripe_oauth') && $tenant) {

            try {

                $provider = new Stripe([
                    'clientId' => config('services.stripe.client_id'),
                    'clientSecret' => config('cashier.secret'),
                    'redirectUri' => route('central.tenants.setup_stripe_redirect'),
                ]);

                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $request->input('code')
                ]);

                $tokenString = $token->getToken();

                $tenant->setOption('STRIPE_ACCOUNT_ID', $tokenString);

                $request->session()->flash('success', 'Connected to Stripe successfully.');

            } catch (\Exception $e) {
                $request->session()->flash('warning', 'We were unable to connect to your Stripe account.');
            }
        } else {
            $request->session()->flash('warning', 'You did not connect a Stripe account.');
        }

        if ($tenant) {
            return Inertia::location(route('central.tenants.stripe', $tenant));
        } else {
            return Inertia::location(route('central.tenants'));
        }
    }

    public function billing(Tenant $tenant, Request $request)
    {
        $paymentMethod = $tenant->defaultPaymentMethod();
        $invoices = $tenant->invoicesIncludingPending([
            'limit' => 5,
        ]);

        return Inertia::render('Central/Tenants/Billing', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'payment_method' => $paymentMethod,
            'invoices' => $invoices,
        ]);
    }

    public function addPaymentMethod(Tenant $tenant) {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        abort_unless($tenant->stripe_id, 404);

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card', 'bacs_debit'],
            'mode' => 'setup',
            'customer' => $tenant->stripe_id,
            'success_url' => route('central.tenants.billing.add-method-success', $tenant),
            'cancel_url' => route('central.tenants.billing', $tenant),
            'locale' => 'en-GB',
            'metadata' => [
                'session_type' => 'direct_debit_setup',
            ],
        ]);

        return Inertia::location($session->url);
    }

    public function addPaymentMethodSuccess(Tenant $tenant) {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        return Inertia::location(route('central.tenants.billing', $tenant));
    }

    public function stripeBillingPortal(Tenant $tenant, Request $request) {
        return Inertia::location($tenant->billingPortalUrl(route('central.tenants.billing', $tenant)));
    }

}
