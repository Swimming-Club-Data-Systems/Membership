<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PointOfSaleController extends Controller
{
    public function index(Request $request)
    {
        $readerId = $request->session()->get('pos.reader_id');

        return Inertia::render('Payments/PointOfSale/PointOfSale', [
            'reader' => $readerId,
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

    public function connectReader()
    {

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

        $attempt = 0;
        $tries = 3;
        $shouldRetry = false;

        do {
            $attempt++;
            try {
                $reader = $stripe->terminal->readers->processPaymentIntent($readerId, [
                    'payment_intent' => $intentId,
                ]);

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
