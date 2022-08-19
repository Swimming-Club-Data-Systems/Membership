<?php

$tenant = app()->tenant;

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
    $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorInvalidRequest'] = true;
  } else {
    try {
      // Get renewal
      $db = app()->db;
      
      include 'GetRenewal.php';

      $progress = $db->prepare("UPDATE `renewalProgress` SET `Stage` = `Stage` + 1, `Substage` = 0 WHERE `RenewalID` = ? AND `UserID` = ?");
      $progress->execute([
        $renewal,
        $person
      ]);
      $_SESSION['TENANT-' . app()->tenant->getId()]['Successful'] = true;
    } catch (Exception $e) {
      // Catches halt
      $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorNoReg'] = true;
    }
  }
} catch (Exception $e) {

} finally {
  header("Location: " . autoUrl("users/" . $person . "/authorise-direct-debit-opt-out"));
}