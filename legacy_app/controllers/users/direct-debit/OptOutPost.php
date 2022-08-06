<?php

$tenant = tenant()->getLegacyTenant();

$checkUser = $db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ? AND Tenant = ?");
$checkUser->execute([
  $person,
  $tenant->getId()
]);

if ($checkUser->fetchColumn() == 0) {
  halt(404);
}

try {

  if (!\SCDS\FormIdempotency::verify() || !\SCDS\CSRF::verify()) {
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['ErrorInvalidRequest'] = true;
  } else {
    try {
      // Get renewal
      $db = DB::connection()->getPdo();
      
      include 'GetRenewal.php';

      $progress = $db->prepare("UPDATE `renewalProgress` SET `Stage` = `Stage` + 1, `Substage` = 0 WHERE `RenewalID` = ? AND `UserID` = ?");
      $progress->execute([
        $renewal,
        $person
      ]);
      $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Successful'] = true;
    } catch (Exception $e) {
      // Catches halt
      $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['ErrorNoReg'] = true;
    }
  }
} catch (Exception $e) {

} finally {
  header("Location: " . autoUrl("users/" . $person . "/authorise-direct-debit-opt-out"));
}