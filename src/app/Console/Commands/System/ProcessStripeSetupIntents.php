<?php

namespace App\Console\Commands\System;

use App\Exceptions\NoStripeAccountException;
use App\Models\Central\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Stripe\SetupIntent;

class ProcessStripeSetupIntents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:process_setup_intents {tenant} {date}';

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
                echo 'Tenant '.$tenant->id.' does not have a Stripe account';

                return Command::FAILURE;
            }

            $date = Carbon::create($this->argument('date'));

            $intents = SetupIntent::all(
                [
                    'created' => [
                        'gt' => $date->timestamp,
                    ],
                    'expand' => ['data.payment_method', 'data.payment_method.billing_details.address', 'data.mandate'],
                ],
                [
                    'stripe_account' => $tenant->stripeAccount(),
                ]
            );

            $intentsIterator = $intents->autoPagingIterator();

            $numErrors = 0;
            $numSuccess = 0;

            foreach ($intentsIterator as $data) {
                if ($data->status == 'succeeded' && $data?->payment_method?->type == 'bacs_debit' && $data->mandate) {
                    try {
                        DB::table('stripeMandates')->upsert([
                            'ID' => $data->payment_method->id,
                            'Customer' => $data->customer,
                            'Mandate' => $data->mandate->id,
                            'Fingerprint' => $data->payment_method->bacs_debit->fingerprint,
                            'Last4' => $data->payment_method->bacs_debit->last4,
                            'SortCode' => $data->payment_method->bacs_debit->sort_code,
                            'Address' => json_encode($data->payment_method->billing_details->address),
                            'Status' => $data->mandate->payment_method_details->bacs_debit->network_status,
                            'MandateStatus' => $data->mandate->status,
                            'Reference' => $data->mandate->payment_method_details->bacs_debit->reference,
                            'URL' => $data->mandate->payment_method_details->bacs_debit->url,
                        ], [
                            'ID',
                        ], [
                            'Reference', 'Status', 'MandateStatus', 'URL', 'SortCode', 'Last4', 'Address',
                        ]);
                        $numSuccess++;
                    } catch (\Exception $e) {
                        report($e);
                        $numErrors++;
                    }
                }
            }

            echo 'Completed with '.$numSuccess.' successes and '.$numErrors.' errors.';
        });

        return Command::SUCCESS;
    }
}
