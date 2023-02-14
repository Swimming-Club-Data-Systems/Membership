<?php

namespace App\Console\Commands\System;

use App\Exceptions\NoStripeAccountException;
use App\Models\Central\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;

class ProcessStripePaymentIntents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:process_payment_intents {tenant} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::find($this->argument('tenant'));

        $tenant->run(function () use ($tenant) {
            try {
                $tenant->stripeAccount();
            } catch (NoStripeAccountException) {
                echo("Tenant " . $tenant->id . " does not have a Stripe account");
                return Command::FAILURE;
            }

            $date = Carbon::create($this->argument('date'));

            $intents = PaymentIntent::all(
                [
                    'created' => [
                        'gt' => $date->timestamp,
                    ],
                    'expand' => ['data.customer', 'data.payment_method', 'data.charges.data.balance_transaction'],
                ],
                [
                    'stripe_account' => $tenant->stripeAccount(),
                ]
            );

            $intentsIterator = $intents->autoPagingIterator();

            $numErrors = 0;
            $numSuccess = 0;

            foreach ($intentsIterator as $data) {
                /**
                 * @var PaymentIntent $data
                 */
                if ($data?->metadata?->payment_category == 'monthly_fee') {
                    try {
                        DB::beginTransaction();
                        $fee = 0;
                        if ($data?->charges?->data[0]?->balance_transaction) {
                            $fee = $data?->charges?->data[0]?->balance_transaction->fee;
                        }

                        DB::table('payments')->where('stripePaymentIntent', $data->id)->update([
                            'Status' => $data->status,
                            'stripeFee' => $fee,
                        ]);

                        $payment = DB::table('payments')->where('stripePaymentIntent', $data->id)->first();

                        if ($payment) {
                            DB::table('paymentsPending')->where('Payment', $payment->PaymentID)->update([
                                'Status' => 'Paid',
                            ]);
                        }

                        DB::commit();

                        $numSuccess++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        report($e);
                        $numErrors++;
                    }
                }
            }

            echo "Completed with " . $numSuccess . " successes and " . $numErrors . " errors.";
        });

        return Command::SUCCESS;
    }
}
