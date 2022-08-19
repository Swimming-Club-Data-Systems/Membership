<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

// Get membership years
$today = new DateTime('now', new DateTimeZone('Europe/London'));

$renewal = \SCDS\Onboarding\Renewal::retrieve($id);

if (!$renewal) halt(404);

header("content-type: application/json");

// Get sessions to reset
$getSessions = $db->prepare("SELECT `onboardingSessions`.`user`, `onboardingSessions`.`id`, `onboardingSessions`.`batch` FROM `onboardingSessions` WHERE `renewal` = ? AND `status` IN ('pending', 'in_progress', 'not_ready')");
$getSessions->execute([
  $id,
]);

$remove = $db->prepare("UPDATE `onboardingSessions` SET `status` = ?, `completed_at` = ?, `renewal` = ? WHERE `id` = ?");
$cancelBatch  = $db->prepare("UPDATE `membershipBatch` SET `Cancelled` = ? WHERE `ID` = ?");

$db->beginTransaction();

try {

  while ($session = $getSessions->fetch(PDO::FETCH_OBJ)) {

    // Remove the existing session by setting complete and removing renewal link
    $remove->execute([
      'complete',
      (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
      null,
      $session->id,
    ]);

    // Cancel batch
    if ($session->batch) {
      $cancelBatch->execute([
        (int) true,
        $session->batch,
      ]);
    }

    $renewal->generateSession($session->user);
  }

  $db->commit();
  echo json_encode(['message' => 'Success']);
} catch (Exception $e) {
  $db->rollBack();

  echo json_encode(['message' => $e->getMessage() . $e->getLine()]);
}
