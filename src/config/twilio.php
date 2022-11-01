<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Account SID
    |--------------------------------------------------------------------------
    |
    | The account SID in Twilio
    |
    */

    'sid' => env('TWILIO_SID'),

    /*
    |--------------------------------------------------------------------------
    | Auth Token
    |--------------------------------------------------------------------------
    |
    | The Auth token for the given Twilio Account SID
    |
    */

    'token' => env('TWILIO_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | From Number
    |--------------------------------------------------------------------------
    |
    | The default from number used by Twilio requests
    |
    */

    'from' => env('TWILIO_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Edge Location
    |--------------------------------------------------------------------------
    |
    | The edge location used for Twilio requests, defaults to dublin.
    |
    */

    'edge' => env('TWILIO_EDGE_LOCATION', 'dublin'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Region
    |--------------------------------------------------------------------------
    |
    | The region used for Twilio requests, defaults to null for Twilio default.
    |
    */

    'region' => env('TWILIO_REGION', null),

];
