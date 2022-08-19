<?php

use function GuzzleHttp\json_encode;

$db = app()->db;
$tenant = app()->tenant;

if (!isset($_POST['state']) || !isset($_POST['member']) || !isset($_POST['squad'])) {
  halt(404);
}

$error = null;
$status = 200;

try {
  // Validate squad and member
  $getMemberCount = $db->prepare("SELECT COUNT(*) FROM members WHERE MemberID = ? AND Tenant = ?");
  $getMemberCount->execute([
    $_POST['member'],
    $tenant->getId(),
  ]);

  if ($getMemberCount->fetchColumn() == 0) {
    throw new Exception('No such member');
  }

  $getSquadCount = $db->prepare("SELECT COUNT(*) FROM squads WHERE SquadID = ? AND Tenant = ?");
  $getSquadCount->execute([
    $_POST['squad'],
    $tenant->getId(),
  ]);

  if ($getSquadCount->fetchColumn() == 0) {
    throw new Exception('No such squad');
  }

  if (bool($_POST['state'])) {
    // We want to put them in the squad

    // Get count
    $getCount = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Squad = ? AND Member = ?");
    $getCount->execute([
      $_POST['squad'],
      $_POST['member'],
    ]);

    if ($getCount->fetchColumn() == 0) {
      $add = $db->prepare("INSERT INTO squadMembers (Member, Squad, Paying) VALUES (?, ?, ?)");
      $add->execute([
        $_POST['member'],
        $_POST['squad'],
        (int) true,
      ]);
    }
  } else {
    // We want to remove them from the squad
    $remove = $db->prepare("DELETE FROM squadMembers WHERE Member = ? AND Squad = ?");
    $remove->execute([
      $_POST['member'],
      $_POST['squad'],
    ]);
  }
} catch (Exception $e) {

  $status = 500;
  $error = $e->getMessage();

  if (get_class($e) == 'PDOException') {
    $error = 'A database error occurred';
  }
}

http_response_code(200);
header('content-type: application/json');
echo json_encode([
  'status' => $status,
  'error' => $error,
]);
