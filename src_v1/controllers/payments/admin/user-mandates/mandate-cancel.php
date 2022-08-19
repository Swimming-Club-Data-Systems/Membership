<?php

// CANCEL A MANDATE

require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;
$tenant = app()->tenant;

$deleteMandatePref = $db->prepare("DELETE FROM paymentPreferredMandate WHERE UserID = ? AND MandateID = ?");
$getUser = $db->prepare("SELECT UserID, MandateID FROM paymentMandates WHERE Mandate = ?");
$getRemainingMandates = $db->prepare("SELECT MandateID FROM paymentMandates WHERE UserID = ? AND InUse = 1");
$setDisabled = $db->prepare("UPDATE paymentMandates SET InUse = 0 WHERE MandateID = ?");
$getCountPref = $db->prepare("SELECT COUNT(*) FROM paymentPreferredMandate WHERE UserID = ?");
$setPrefMandate = $db->prepare("INSERT INTO paymentPreferredMandate (UserID, MandateID) VALUES (?, ?)");

header("content-type: application/json");

$data = [];

try {
  $db->beginTransaction();
  $mandateObj = $client->mandates()->cancel($mandate);

  $data = [
    'status' => 200,
    'message' => null,
    'deleted' => true
  ];

  $getUser->execute([$mandate]);
  $user = $getUser->fetch(PDO::FETCH_ASSOC);
  if ($user != null) {

    // Verify user
    $getUserCount = $db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ? AND Tenant = ?");
    $getUserCount->execute([
      $user['UserID'],
      $tenant->getId()
    ]);
    if ($getUserCount->fetchColumn() == 0) {
      throw new Exception('User ' . $user['UserID'] . ' does not exist in this tenant');
    }

    // Delete if this is preferred mandate
    $deleteMandatePref->execute([
      $user['UserID'],
      $user['MandateID']
    ]);
    $setDisabled->execute([
      $user['MandateID']
    ]);
    $getRemainingMandates->execute([
      $user['UserID']
    ]);
    $newPrefMandate = $getRemainingMandates->fetchColumn();
    $countPref = $getCountPref->execute([
      $user['UserID']
    ]);
    if ($newPrefMandate != null && $getCountPref->fetchColumn() == 0) {
      $setPrefMandate->execute([
        $user['UserID'],
        $newPrefMandate
      ]);
    }
  }

  if (app('request')->method == 'GET' && $user != null) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['MandateDeletedTrue'] = true;
    header("location: " . autoUrl("users/" . $user['UserID'] . "/mandates"));
  }

  $db->commit();
} catch (\GoCardlessPro\Core\Exception\ApiException $e) {
  // Api request failed / record couldn't be created.
  $db->rollBack();

  $code = $e->getCode();
  http_response_code($code);
  $data = [
    'status' => $code,
    'message' => $e->getMessage(),
    'deleted' => false
  ];
} catch (\GoCardlessPro\Core\Exception\MalformedResponseException $e) {
  // Unexpected non-JSON response
  $db->rollBack();

  $code = $e->getCode();
  http_response_code($code);
  $data = [
    'status' => $code,
    'message' => $e->getMessage(),
    'deleted' => false
  ];
} catch (\GoCardlessPro\Core\Exception\ApiConnectionException $e) {
  // Network error
  $db->rollBack();

  $code = $e->getCode();
  http_response_code($code);
  $data = [
    'status' => $code,
    'message' => $e->getMessage(),
    'deleted' => false
  ];
} catch (Exception $e) {
  $db->rollBack();

  http_response_code(500);
  $data = [
    'status' => 500,
    'message' => $e->getMessage(),
    'deleted' => false
  ];
}

echo json_encode($data, JSON_PRETTY_PRINT);