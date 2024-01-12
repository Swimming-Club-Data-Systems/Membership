<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\PointOfSaleItem;
use App\Models\Tenant\PointOfSaleItemGroup;
use App\Models\Tenant\PointOfSaleScreen;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PointOfSaleController extends Controller
{
    public function show(Request $request, PointOfSaleScreen $screen)
    {
        $readerId = $request->session()->get('pos.reader_id');

        if (! $readerId) {
            abort(400, 'You must connect a reader.');
        }

        return Inertia::render('Payments/PointOfSale/PointOfSale', [
            'id' => $screen->id,
            'reader_id' => $readerId,
            'item_groups' => $screen->pointOfSaleItemGroups->map(function (PointOfSaleItemGroup $group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'items' => $group->pointOfSaleItems->map(function (PointOfSaleItem $item) {
                        return [
                            'id' => $item->id,
                            'label' => $item->label,
                            'price_id' => $item->price->id,
                            'stripe_price_id' => $item->price->stripe_id,
                            'unit_amount' => $item->price->unit_amount,
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function listReaders(Request $request)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $readers = $stripe->terminal->readers->all(['limit' => 3]);

            $reader = collect($readers->data)->firstOrFail();

            $request->session()->put('pos.reader_id', $reader->id);

            return 'Reader: '.$reader->label.' ('.$reader->id.') selected';
        } catch (\Exception $e) {
            return 'No reader has been found: '.$e->getMessage();
        }
    }

    public function connectReader(Request $request, string $readerId)
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));

            $reader = $stripe->terminal->readers->retrieve($readerId, [

            ], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);

            $request->session()->put('pos.reader_id', $reader->id);

            return [
                'selected_reader' => $reader->id,
                'reader' => $reader,
            ];
        } catch (\Exception $e) {
            return 'No reader has been found: '.$e->getMessage();
        }
    }

    public function clearReader(Request $request, string $readerId)
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        try {
            $stripe->terminal->readers->cancelAction($readerId, [], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);

            $request->session()->remove('pos.current_intent_id');
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
        ];
    }

    public function setReaderDisplay(Request $request, string $readerId)
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        $lineItems = [];

        $items = collect($request->json('items'));

        $total = 0;

        foreach ($items as $item) {
            $total = $total + ($item['quantity'] * $item['unit_amount']);

            $lineItems[] = [
                'amount' => $item['unit_amount'],
                'description' => $item['label'],
                'quantity' => $item['quantity'],
            ];
        }

        if (count($lineItems) > 0) {
            $stripe->terminal->readers->setReaderDisplay(
                $readerId,
                [
                    'type' => 'cart',
                    'cart' => [
                        'currency' => 'gbp',
                        'line_items' => $lineItems,
                        'tax' => 0,
                        'total' => $total,
                    ],
                ],
                [
                    'stripe_account' => $tenant->stripeAccount(),
                ]
            );
        } else {
            $stripe->terminal->readers->cancelAction($readerId, [], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);
        }

        return [
            'success' => true,
        ];
    }

    public function createIntent(Request $request)
    {
        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        $intent = $stripe->paymentIntents->create([
            'amount' => 1000,
            'currency' => 'gbp',
            'payment_method_types' => [
                'card_present',
            ],
            'capture_method' => 'manual',
        ]);

        $request->session()->put('pos.current_intent_id', $intent->id);
    }

    public function charge(Request $request)
    {
        $readerId = $request->session()->get('pos.reader_id');
        $intentId = $request->session()->get('pos.current_intent_id');

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        /** @var Tenant $tenant */
        $tenant = tenant();

        $lineItems = [];

        $items = collect($request->json('items'));

        $total = 0;

        foreach ($items as $item) {
            $total = $total + ($item['quantity'] * $item['unit_amount']);

            $lineItems[] = [
                'amount' => $item['unit_amount'],
                'description' => $item['label'],
                'quantity' => $item['quantity'],
            ];
        }

        if ($intentId) {
            $stripe->paymentIntents->update($intentId, [
                'amount' => $total,
            ], ['stripe_account' => $tenant->stripeAccount()]);
        } else {
            $intent = $stripe->paymentIntents->create([
                'amount' => $total,
                'currency' => 'gbp',
                'payment_method_types' => [
                    'card_present',
                ],
                'capture_method' => 'manual',
            ], ['stripe_account' => $tenant->stripeAccount()]);

            $intentId = $intent->id;

            $request->session()->put('pos.current_intent_id', $intentId);
        }

        $attempt = 0;
        $tries = 3;
        $shouldRetry = false;

        do {
            $attempt++;
            try {
                if (count($lineItems) > 0) {
                    $stripe->terminal->readers->setReaderDisplay(
                        $readerId,
                        [
                            'type' => 'cart',
                            'cart' => [
                                'currency' => 'gbp',
                                'line_items' => $lineItems,
                                'tax' => 0,
                                'total' => $total,
                            ],
                        ],
                        [
                            'stripe_account' => $tenant->stripeAccount(),
                        ]
                    );
                }

                $reader = $stripe->terminal->readers->processPaymentIntent($readerId, [
                    'payment_intent' => $intentId,
                ], ['stripe_account' => $tenant->stripeAccount()]);

                echo json_encode($reader);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                switch ($e->getStripeCode()) {
                    case 'terminal_reader_timeout':
                        // Temporary networking blip, automatically retry a few times.
                        if ($attempt == $tries) {
                            $shouldRetry = false;
                            echo json_encode(['error' => $e->getMessage()]);
                        } else {
                            $shouldRetry = true;
                        }
                        break;
                    case 'terminal_reader_offline':
                        // Reader is offline and won't respond to API requests. Make sure the reader is powered on
                        // and connected to the internet before retrying.
                        $shouldRetry = false;
                        echo json_encode(['error' => $e->getMessage()]);
                        break;
                    case 'terminal_reader_busy':
                        // Reader is currently busy processing another request, installing updates or changing settings.
                        // Remember to disable the pay button in your point-of-sale application while waiting for a
                        // reader to respond to an API request.
                        $shouldRetry = false;
                        echo json_encode(['error' => $e->getMessage()]);
                        break;
                    case 'intent_invalid_state':
                        // Check PaymentIntent status because it's not ready to be processed. It might have been already
                        // successfully processed or canceled.
                        $shouldRetry = false;
                        $paymentIntent = $stripe->paymentIntents->retrieve($intent->id);
                        echo json_encode(['error' => 'PaymentIntent is already in '.$paymentIntent->status.' state.']);
                        break;
                    default:
                        $shouldRetry = false;
                        echo json_encode(['error' => $e->getMessage()]);
                        break;
                }
            }
        } while ($shouldRetry);
    }

    public function presentToReader(Request $request)
    {
        $readerId = $request->session()->get('pos.reader_id');
        $intentId = $request->session()->get('pos.current_intent_id');

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        $reader = $stripe->testHelpers->terminal->readers->presentPaymentMethod($readerId);
    }
}
