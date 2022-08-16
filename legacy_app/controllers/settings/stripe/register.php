<?php

$tenant = tenant()->getLegacyTenant();
$user = Auth::User()->getLegacyUser();

if ($at = tenant()->getLegacyTenant()->getStripeAccount()) {
  // Already go it, halt
  halt(404);
}

http_response_code(302);
header('location: ' . platformUrl('services/stripe/connect?tenant=' . urlencode($tenant->getUUID()) . '&user=' . urlencode($user->getId()) . ''));