<?php

namespace App\Http\Controllers\Central;

use App\Business\Helpers\Money;
use App\Business\Helpers\PaymentMethod;
use App\Business\OAuthProviders\Stripe;
use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Laravel\Cashier\Invoice;
use Laravel\Cashier\Subscription;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\Intl\Currencies;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $tenants = null;
        if (Gate::allows('manage')) {
            $tenants = Tenant::orderBy('Name', 'asc')->with(['tenantOptions' => function ($query) {
                $query->where('Option', 'LOGO_DIR');
            }])->paginate(config('app.per_page'));
        } else {
            $user = $request->user('central');
            $tenants = $user->tenants()->orderBy('Name', 'asc')->with(['tenantOptions' => function ($query) {
                $query->where('Option', 'LOGO_DIR');
            }])->paginate(config('app.per_page'));
        }

        return Inertia::render('Central/Tenants/Index', [
            'tenants' => $tenants->onEachSide(3),
        ]);
    }

    public function show(Tenant $tenant, Request $request)
    {
        $this->authorize('manage', $tenant);

        return Inertia::render('Central/Tenants/Show', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'form_initial_values' => [
                'name' => $tenant->Name,
                'code' => $tenant->Code,
                'website' => $tenant->Website,
                'email' => $tenant->Email,
                'verified' => (bool) $tenant->Verified,
                'domain' => $tenant->Domain,
                'alphanumeric_sender_id' => (string) $tenant->alphanumeric_sender_id,
                'application_fee_type' => $tenant->application_fee_type ?? 'none',
                'application_fee_amount' => $tenant->application_fee_type != 'none' &&
                mb_strlen((string) $tenant->application_fee_amount) > 0 ?
                    BigDecimal::of((string) $tenant->application_fee_amount)->withPointMovedLeft(2) : 0,
            ],
            'editable' => Gate::allows('manage'),
        ]);
    }

    public function save(Tenant $tenant, Request $request)
    {
        $this->authorize('manage', $tenant);

        $validated = $request->validate([
            'name' => ['required', 'max:128'],
            'code' => ['required', 'size:4'],
            'email' => ['required', 'email:rfc,dns', 'max:256'],
            'website' => ['required', 'url', 'max:256'],
            'verified' => ['sometimes', 'required', 'boolean'],
            'domain' => ['sometimes', 'required', 'max:256'],
            'alphanumeric_sender_id' => ['max:11'],
            'application_fee_type' => ['sometimes', 'required', Rule::in(['none', 'fixed', 'percent'])],
            'application_fee_amount' => ['sometimes', 'required_unless:application_fee_type,none', 'min:0'],
        ]);

        $tenant->Name = $request->input('name');
        $tenant->Code = Str::upper($request->input('code'));
        $tenant->Email = $request->input('email');
        $tenant->Website = $request->input('website');
        if (Gate::allows('manage')) {
            $tenant->Verified = $request->input('verified');
            $tenant->Domain = $request->input('domain');
            if ($request->input('application_fee_type') === 'fixed' || $request->input('application_fee_type') === 'percent') {
                $tenant->application_fee_type = $request->input('application_fee_type');
                $tenant->application_fee_amount =
                    BigDecimal::of((string) $request->input('application_fee_amount'))->withPointMovedRight(2)->toInt();
            } else {
                $tenant->application_fee_type = 'none';
                $tenant->application_fee_amount = 0;
            }
        }
        $tenant->alphanumeric_sender_id = $request->input('alphanumeric_sender_id', null);
        $tenant->save();

        $request->session()->flash('success', "We've saved your changes to {$tenant->Name}.");

        return Redirect::route('central.tenants.show', $tenant);
    }

    public function stripe(Tenant $tenant, Request $request)
    {
        $this->authorize('manage', $tenant);

        return Inertia::render('Central/Tenants/Stripe', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'stripe_account' => $tenant->getOption('STRIPE_ACCOUNT_ID'),
        ]);
    }

    public function stripeOAuthStart(Tenant $tenant, Request $request)
    {
        $this->authorize('manage', $tenant);

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
                    'code' => $request->input('code'),
                ]);

                $responseValues = $token->getValues();

                if (! isset($responseValues['stripe_user_id'])) {
                    throw new \Exception('No stripe_user_id returned in response');
                }

                $tenant->setOption('STRIPE_ACCOUNT_ID', $responseValues['stripe_user_id']);

                if ($tenant->Domain) {
                    // Setup Apple Pay domains
                    $stripe = new \Stripe\StripeClient(config('cashier.secret'));

                    $stripe->applePayDomains->create([
                        'domain_name' => $tenant->Domain,
                    ], [
                        'stripe_account' => $responseValues['stripe_user_id'],
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
        $this->authorize('manage', $tenant);

        return Inertia::render('Central/Tenants/Billing', [
            'id' => fn () => $tenant->ID,
            'name' => fn () => $tenant->Name,
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
                        'currency_name' => Currencies::exists(Str::upper($stripeSubscription->currency)) ? Currencies::getName(Str::upper($stripeSubscription->currency)) : 'N/A',
                        'description' => $stripeSubscription->description,
                        'billing_cycle_anchor' => $stripeSubscription->billing_cycle_anchor,
                        'collection_method' => $stripeSubscription->collection_method,
                        'discount' => (bool) $stripeSubscription->discount,
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
                                    'unit_amount_period' => Money::formatCurrency($subItem->price->unit_amount, $subItem->price->currency).' '
                                        .Str::upper($subItem->price->currency).' / '.$subItem->price->recurring->interval,
                                    'amount_period' => Money::formatCurrency($subItem->price->unit_amount * $subItem->quantity, $subItem->price->currency).' '
                                        .Str::upper($subItem->price->currency).' / '.$subItem->price->recurring->interval,
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

    public function addPaymentMethod(Tenant $tenant)
    {
        $this->authorize('manage', $tenant);

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

    public function addPaymentMethodSuccess(Tenant $tenant)
    {
        $this->authorize('manage', $tenant);

        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        return Inertia::location(route('central.tenants.billing', $tenant));
    }

    public function stripeBillingPortal(Tenant $tenant, Request $request)
    {
        $this->authorize('manage', $tenant);

        return Inertia::location($tenant->billingPortalUrl(route('central.tenants.billing', $tenant)));
    }

    public function payAsYouGo(Tenant $tenant)
    {
        if (! $tenant->journal) {
            try {
                $tenant->initJournal();
                $tenant = $tenant->fresh();
            } catch (JournalAlreadyExists $e) {
                // Ignore, we already checked existence
            }
        }

        $balance = $tenant->journal->getBalance();

        return Inertia::render('Central/Tenants/PayAsYouGo', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'formatted_balance' => Money::formatCurrency($balance->getAmount(), $balance->getCurrency()),
            'balance' => $balance->getAmount(),
            'currency' => $balance->getCurrency(),
        ]);
    }

    public function topUp(Tenant $tenant)
    {
        abort_unless(config('custom.top_up_price') != null, 404, 'The Price ID for top ups has not been defined');

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        $checkoutSession = $stripe->checkout->sessions->create([
            'success_url' => route('central.tenants.top_up_success', $tenant).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('central.tenants.pay_as_you_go', [$tenant]),
            'line_items' => [
                [
                    'price' => config('custom.top_up_price'),
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'submit_type' => 'pay',
            'customer' => $tenant->stripe_id,
            'client_reference_id' => $tenant->ID,
            'metadata' => [
                'type' => 'tenant_account_top_up',
                'tenant' => $tenant->ID,
            ],
        ]);

        return Inertia::location($checkoutSession->url);
    }

    public function topUpSuccess(Tenant $tenant, Request $request)
    {
        if ($request->input('session_id')) {
            try {
                $stripe = new \Stripe\StripeClient(config('cashier.secret'));
                $session = $stripe->checkout->sessions->retrieve($request->input('session_id'), [
                    'expand' => ['payment_intent'],
                ]);

                if ($session->status == 'complete' && $session->payment_intent) {
                    $request->session()->flash('success', 'You have topped up your balance by '.Money::formatCurrency($session->payment_intent->amount, $session->payment_intent->currency).'. It may take a moment for your balance to update.');
                }
            } catch (\Exception $e) {
            }
        }

        return Redirect::route('central.tenants.pay_as_you_go', [$tenant]);
    }

    public function applePayDomains(Tenant $tenant, Request $request)
    {
        $this->authorize('manage', $tenant);

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        $stripeAccount = $tenant->getOption('STRIPE_ACCOUNT_ID');

        $applePay = null;
        if ($stripeAccount) {
            try {
                $applePay = $stripe->applePayDomains->all([
                    'limit' => 20,
                ], [
                    'stripe_account' => $stripeAccount,
                ]);
            } catch (ApiErrorException $e) {
                // Swallow
            }
        }

        return Inertia::render('Central/Tenants/ApplePayDomains', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'stripe_account' => $tenant->getOption('STRIPE_ACCOUNT_ID'),
            'apple_pay_domains' => $applePay?->data,
        ]);
    }

    public function addApplePayDomain(Tenant $tenant, Request $request)
    {
        $this->authorize('manage', $tenant);

        $stripeAccount = $tenant->getOption('STRIPE_ACCOUNT_ID');

        abort_unless($stripeAccount, 404, 'This tenant does not have a connected Stripe Account');

        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));

            $domain = $stripe->applePayDomains->create([
                'domain_name' => $request->input('domain'),
            ], [
                'stripe_account' => $stripeAccount,
            ]);

            $request->session()->flash('success', 'We have added '.$domain->domain_name.' to the list of Apple Pay domains.');
        } catch (\Exception $e) {
            $request->session()->flash('error', $e->getMessage());
        }

        return Redirect::route('central.tenants.apple_pay_domains', $tenant);
    }

    public function deleteApplePayDomain(Tenant $tenant, $id, Request $request)
    {
        $this->authorize('manage', $tenant);

        $stripeAccount = $tenant->getOption('STRIPE_ACCOUNT_ID');

        abort_unless($stripeAccount, 404, 'This tenant does not have a connected Stripe Account');

        try {
            $domain = \Stripe\ApplePayDomain::retrieve($id, [
                'stripe_account' => $stripeAccount,
            ]);

            $domainName = $domain->domain_name;

            $domain->delete();

            $request->session()->flash('success', 'We have deleted '.$domainName.' from the list of Apple Pay domains.');
        } catch (\Exception $e) {
            $request->session()->flash('error', $e->getMessage());
        }

        return Redirect::route('central.tenants.apple_pay_domains', $tenant);
    }
}
