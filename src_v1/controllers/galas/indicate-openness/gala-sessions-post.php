<?php

$db = app()->db;
$tenant = app()->tenant;

$galaDetails = $db->prepare("SELECT GalaName `name`, GalaDate `ends` FROM galas WHERE GalaID = ? AND Tenant = ?");
$galaDetails->execute([
  $id,
  $tenant->getId()
]);
$gala = $galaDetails->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$session = $getSessions->fetch(PDO::FETCH_ASSOC);

if ($nowDate < $galaDate) {

  if ($session == null && isset($_POST['numSessions'])) {
    $insertSessions = $db->prepare("INSERT INTO galaSessions (`Gala`, `Name`) VALUES (?, ?)");
    $num = (int) $_POST['numSessions'];
    for ($i = 0; $i < $num; $i++) {
      $sessionName = 'Session ' . ($i+1);
      pre($sessionName);
      try {
        $insertSessions->execute([
          $id,
          $sessionName
        ]);
        $_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus'] = true;
      } catch (Exception $e) {
        pre($e);
        $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus'] = true;
      }
    }
  } else {
    $updateSessions = $db->prepare("UPDATE galaSessions SET `Name` = ? WHERE ID = ?");
    do {
      if (isset($_POST['session-' . $session['ID']]) && mb_strlen(trim($_POST['session-' . $session['ID']])) > 0) {
        try {
          $updateSessions->execute([
            trim($_POST['session-' . $session['ID']]),
            $session['ID']
          ]);
          $_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus'] = true;
        } catch (Exception $e) {
          $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus'] = true;
        }
      }
    } while ($session = $getSessions->fetch(PDO::FETCH_ASSOC));

    if (isset($_POST['newSession']) && mb_strlen(trim($_POST['newSession'])) > 0) {
      try {
        $insertSessions = $db->prepare("INSERT INTO galaSessions (`Gala`, `Name`) VALUES (?, ?)");
        $insertSessions->execute([
          $id,
          trim($_POST['newSession'])
        ]);
        $_SESSION['TENANT-' . app()->tenant->getId()]['SuccessStatus'] = true;
      } catch (Exception $e) {
        $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStatus'] = true;
      }
    }
  }

}

header("Location: " . autoUrl("galas/" . $id . "/sessions"));