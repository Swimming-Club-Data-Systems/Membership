<?php

$db = app()->db;
$tenant = app()->tenant;

// Get mandates
$getMandates = $db->prepare("SELECT `URL` FROM stripeMandates WHERE Customer = ? AND ID = ?");
$getMandates->execute([
  app()->user->getStripeCustomer()->id,
  $id,
]);
$url = $getMandates->fetchColumn();

if (!$url) {
  halt(404);
} else {
  http_response_code(302);
  header("location: " . $url);
}