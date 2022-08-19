<?php

$tenant = app()->tenant;
$user = app()->user;

if ($at = app()->tenant->getStripeAccount()) {
  // Already go it, halt
  halt(404);
}

http_response_code(302);
header('location: ' . platformUrl('services/stripe/connect?tenant=' . urlencode($tenant->getUUID()) . '&user=' . urlencode($user->getId()) . ''));