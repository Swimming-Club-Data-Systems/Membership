<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

// Get mandates
$getMandates = $db->prepare("SELECT `URL` FROM stripeMandates WHERE Customer = ? AND ID = ?");
$getMandates->execute([
  Auth::User()->getLegacyUser()->getStripeCustomer()->id,
  $id,
]);
$url = $getMandates->fetchColumn();

if (!$url) {
  halt(404);
} else {
  http_response_code(302);
  header("location: " . $url);
}