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

// DELETE
try {
  $apd = \Stripe\ApplePayDomain::retrieve(
    $_GET['id'],
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );
  $apd->delete();
} catch (Exception $e) {
  reportError($e);
}

http_response_code(302);
header("location: " . autoUrl("admin/tenants/$id/stripe"));
