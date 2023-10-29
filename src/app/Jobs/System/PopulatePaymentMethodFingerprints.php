<?php

namespace App\Jobs\System;

use App\Models\Tenant\PaymentMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PopulatePaymentMethodFingerprints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $paymentMethods = PaymentMethod::where('fingerprint', '=', null)->get();

        foreach ($paymentMethods as $method) {
            /** @var PaymentMethod $method */
            try {
                $method->fingerprint = $method->pm_type_data['fingerprint'];
                $method->save();
            } catch (\Exception) {
                // Ignore
            }
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
