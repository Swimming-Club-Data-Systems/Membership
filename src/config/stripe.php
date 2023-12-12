<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Live Mode
    |--------------------------------------------------------------------------
    |
    | Whether the deployment should use livemode or not.
    | Used to check whether to run a webhook job.
    |
    */

    'livemode' => env('STRIPE_LIVEMODE', false),

];
