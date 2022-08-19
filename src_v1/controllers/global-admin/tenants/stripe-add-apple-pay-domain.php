<?php

$db = app()->db;
$getClubs = $db->prepare("SELECT `ID`, `Name`, `Code`, `Verified` FROM tenants WHERE `UniqueID` = ? ORDER BY `Name` ASC");
$getClubs->execute([
  $id
]);
$club = $getClubs->fetch(PDO::FETCH_ASSOC);

if (!$club) halt(404);

$tenant = Tenant::fromId($club['ID']);

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

if (!isset($_POST['apple-pay-domain'])) throw new Exception('No domain supplied');

// DELETE
try {
  $res = \Stripe\ApplePayDomain::create([
    'domain_name' => trim($_POST['apple-pay-domain'])
  ], [
    'stripe_account' => $tenant->getStripeAccount()
  ]);
} catch (Exception $e) {
  reportError($e);
}

http_response_code(302);
header("location: " . autoUrl("admin/tenants/$id/stripe"));
