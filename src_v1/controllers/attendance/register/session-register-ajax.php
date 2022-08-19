<?php

include 'session-register.php';

header('content-type: application/json');

$status = 200;
$html = null;
$error = null;

try {
  $date = new DateTime('now', new DateTimeZone('Europe/London'));
  if (isset($_POST['date'])) {
    try {
      $userDate = new DateTime($_POST['date'], new DateTimeZone('Europe/London'));
      $date = $userDate;
    } catch (Exception $e) {
      throw new Exception('Date error');
    }
  } else {
    throw new Exception('No date supplied');
  }

  if (!isset($_POST['session'])) {
    throw new Exception('No session supplied');
  }
  $sessionId = (int) $_POST['session'];

  try {
    ob_start();
    echo registerSheetGenerator($date, $sessionId);
    $html = ob_get_clean();
  } catch (Exception $e) {
    $status = 500;
  }
} catch (Exception $e) {
  $status = 500;
  $error = $e->getMessage();
}

echo json_encode([
  'status' => $status,
  'html' => trim($html),
  'error' => $error,
]);