<?php

namespace App\Http\Controllers\Central;

use App\Business\Helpers\Money;
use App\Business\Helpers\PaymentMethod;
use App\Business\OAuthProviders\Stripe;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Cashier\Invoice;
use Laravel\Cashier\Subscription;
use Symfony\Component\Intl\Currencies;

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
                'verified' => (bool)$tenant->Verified,
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

                $responseValues = $token->getValues();

                if (!isset($responseValues['stripe_user_id'])) {
                    throw new \Exception("No stripe_user_id returned in response");
                }

                $tenant->setOption('STRIPE_ACCOUNT_ID', $responseValues['stripe_user_id']);

                if ($tenant->Domain) {
                    // Setup Apple Pay domains
                    \Stripe\ApplePayDomain::create([
                        'domain_name' => $tenant->Domain,
                    ], [
                        'stripe_account' => $responseValues['stripe_user_id']
                    ]);
                }

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
        return Inertia::render('Central/Tenants/Billing', [
            'id' => fn() => $tenant->ID,
            'name' => fn() => $tenant->Name,
            'invoices' => function () use ($tenant) {
                return $tenant->invoicesIncludingPending([
                    'limit' => 5,
                ])->map(
                    function (Invoice $item) {
                        return [
                            'id' => $item->asStripeInvoice()->id,
                            'currency' => $item->asStripeInvoice()->currency,
                            'created' => $item->asStripeInvoice()->created,
                            'total' => $item->asStripeInvoice()->total,
                            'money_formatted_total' => Money::formatCurrency($item->asStripeInvoice()->total, $item->asStripeInvoice()->currency),
                            'decimal_formatted_total' => Money::formatDecimal($item->asStripeInvoice()->total, $item->asStripeInvoice()->currency),
                            'link' => $item->asStripeInvoice()->hosted_invoice_url,
                            'pdf_link' => $item->asStripeInvoice()->invoice_pdf,
                        ];
                    }
                );
            },
            'payment_methods' => function () use ($tenant) {
                $paymentMethod = $tenant->defaultPaymentMethod();

                return $tenant->paymentMethods()->merge($tenant->paymentMethods('bacs_debit'))->map(function ($item) use ($paymentMethod) {
                    return [
                        'id' => $item->id,
                        'description' => PaymentMethod::formatName($item),
                        'created' => $item->created,
                        'info_line' => PaymentMethod::formatInfoLine($item),
                        'default' => $item->id === $paymentMethod->id,
                    ];
                });
            },
            'subscriptions' => function () use ($tenant) {
                return $tenant->subscriptions()->with(['items'])->get()->map(function (Subscription $item) {
                    /** @var \Stripe\Subscription $stripeSubscription */
                    $stripeSubscription = $item->asStripeSubscription(['items', 'items.data.price.product', 'latest_invoice', 'default_payment_method']);

                    $name = Str::replaceLast(', ', ', and ', collect($stripeSubscription->items->data)->map(function (\Stripe\SubscriptionItem $subItem) {
                        return $subItem->price->product->name;
                    })->implode(', '));

                    return [
                        'id' => $item->id,
                        'status' => $item->stripe_status,
                        'name' => $name,
                        'current_period_start' => $stripeSubscription->current_period_start,
                        'current_period_end' => $stripeSubscription->current_period_end,
                        'currency' => $stripeSubscription->currency,
                        'currency_name' => Currencies::exists(Str::upper($stripeSubscription->currency)) ? Currencies::getName(Str::upper($stripeSubscription->currency)) : "N/A",
                        'description' => $stripeSubscription->description,
                        'billing_cycle_anchor' => $stripeSubscription->billing_cycle_anchor,
                        'collection_method' => $stripeSubscription->collection_method,
                        'discount' => (bool)$stripeSubscription->discount,
                        'items' => collect($stripeSubscription->items->data)->map(function (\Stripe\SubscriptionItem $subItem) {
                            return [
                                'id' => $subItem->id,
                                'quantity' => $subItem->quantity,
                                'created' => $subItem->created,
                                'price' => [
                                    'billing_scheme' => $subItem->price->billing_scheme,
                                    'unit_amount' => $subItem->price->unit_amount,
                                    'currency' => $subItem->price->currency,
                                    'formatted_unit_amount' => Money::formatCurrency($subItem->price->unit_amount, $subItem->price->currency),
                                    'decimal_unit_amount' => Money::formatDecimal($subItem->price->unit_amount, $subItem->price->currency),
                                    'unit_amount_period' => Money::formatCurrency($subItem->price->unit_amount, $subItem->price->currency) . ' '
                                        . Str::upper($subItem->price->currency) . ' / ' . $subItem->price->recurring->interval,
                                    'amount_period' => Money::formatCurrency($subItem->price->unit_amount * $subItem->quantity, $subItem->price->currency) . ' '
                                        . Str::upper($subItem->price->currency) . ' / ' . $subItem->price->recurring->interval,
                                ],
                                'product_name' => $subItem->price->product->name,
                                'product_type' => $subItem->price->product->type,
                            ];
                        }),
                    ];
                });
            },
        ]);
    }

    public
    function addPaymentMethod(Tenant $tenant)
    {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        abort_unless($tenant->stripe_id, 404);

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card', 'bacs_debit'],
            'mode' => 'setup',
            'customer' => $tenant->stripe_id,
            'success_url' => route('central.tenants.billing.add_payment_method_success', $tenant),
            'cancel_url' => route('central.tenants.billing', $tenant),
            'locale' => 'en-GB',
            'metadata' => [
                'session_type' => 'direct_debit_setup',
            ],
        ]);

        return Inertia::location($session->url);
    }

    public
    function addPaymentMethodSuccess(Tenant $tenant)
    {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        return Inertia::location(route('central.tenants.billing', $tenant));
    }

    public
    function stripeBillingPortal(Tenant $tenant, Request $request)
    {
        return Inertia::location($tenant->billingPortalUrl(route('central.tenants.billing', $tenant)));
    }

}
