<?php

$client = new OAuth2\Client(getenv('GOCARDLESS_CLIENT_ID'), getenv('GOCARDLESS_CLIENT_SECRET'));

$authorizeUrl = $client->getAuthenticationUrl(
    // Once you go live, this should be set to https://connect.gocardless.com. You'll also
    // need to create a live app and update your client ID and secret.
    'https://connect-sandbox.gocardless.com/oauth/authorize',
    'https://membership.myswimmingclub.uk/tenant-services/gc/oauth/callback', // Your redirect URL
    ['scope' => 'read_write',
     'initial_view' => 'login',
     'prefill' => ['email' => 'tim@gocardless.com',
                   'given_name' => 'Tim',
                   'family_name' => 'Rogers',
                   'organisation_name' => 'Tim\'s Fishing Store']]
);

// You'll now want to direct your user to the URL - you could redirect them or display it
// as a link on the page
header("Location: " . $authorizeUrl);
