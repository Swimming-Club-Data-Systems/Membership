<?php

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
$db = app()->db;

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentIntent'])) {
  halt(404);
}

handleCompletedGalaPayments($_SESSION['TENANT-' . app()->tenant->getId()]['GalaPaymentIntent'], true);