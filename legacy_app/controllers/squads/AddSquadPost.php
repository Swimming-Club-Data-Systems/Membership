<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$squadKey = generateRandomString(8);

if (isset($_POST['squadName']) && isset($_POST['squadFee'])) {
  try {
    $insert = $db->prepare("INSERT INTO squads (SquadName, SquadFee, SquadCoach, SquadCoC, SquadTimetable, SquadKey, Tenant) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([
      trim($_POST['squadName']),
      $_POST['squadFee'],
      trim($_POST['squadCoach']),
      $_POST['squadCoC'],
      trim($_POST['squadTimetable']),
      $squadKey,
      $tenant->getId()
    ]);
    $id = $db->lastInsertId();
    header("Location: " . autoUrl('squads/' . $id));
  } catch (PDOException $e) {
    throw new Exception('A database error occurred');
  } catch (Exception $e) {
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['SquadAddError'] = [
      'status' => true,
      'message' => $e->getMessage(),
    ];
  }
} else {
  header("Location: " . autoUrl('squads/addsquad'));
}

?>
