<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'oauth' => [
        'client_id' => env('ADMIN_OAUTH_CLIENT_ID'),
        'client_secret' => env('ADMIN_OAUTH_CLIENT_SECRET'),
        'url_authorize' => env('ADMIN_OAUTH_URL_AUTHORIZE'),
        'url_access_token' => env('ADMIN_OAUTH_URL_ACCESS_TOKEN'),
    ],

    'aad' => [
        'client_id' => env('AAD_OAUTH_CLIENT_ID'),
        'client_secret' => env('AAD_OAUTH_CLIENT_SECRET'),
        'url_authorize' => env('AAD_OAUTH_URL_AUTHORIZE'),
        'url_access_token' => env('AAD_OAUTH_URL_ACCESS_TOKEN'),
    ],

];
