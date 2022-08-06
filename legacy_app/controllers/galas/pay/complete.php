<?php

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
$db = DB::connection()->getPdo();

if (!isset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GalaPaymentIntent'])) {
  halt(404);
}

handleCompletedGalaPayments($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['GalaPaymentIntent'], true);