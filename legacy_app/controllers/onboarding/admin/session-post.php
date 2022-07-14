<?php

// Update details first

$db = app()->db;
$tenant = app()->tenant;

$session = \SCDS\Onboarding\Session::retrieve($id);

if (!\SCDS\CSRF::verify()) {
  halt(403);
}

$states = \SCDS\Onboarding\Session::getStates();

http_response_code(302);

try {

  $start = $session->start->format('Y-m-d');
  if (isset($_POST['start-date'])) {
    $postStart = new DateTime($_POST['start-date'], new DateTimeZone('UTC'));
    $start = $postStart->format('Y-m-d');
  }

  $chargeOutstanding = $session->chargeOutstanding;
  if (isset($_POST['charge-fees']) && $_POST['charge-fees'] == '1') {
    $chargeOutstanding = true;
  } else {
    $chargeOutstanding = false;
  }

  $chargeProRata = $session->chargeProRata;
  if ($chargeOutstanding && isset($_POST['charge-pro-rata']) && $_POST['charge-pro-rata'] == '1') {
    $chargeProRata = true;
  } else {
    $chargeProRata = false;
  }

  $welcomeText = null;
  if (isset($_POST['welcome-text']) && mb_strlen(trim($_POST['welcome-text'])) > 0) {
    $welcomeText = trim($_POST['welcome-text']);
  } else {
    $welcomeText = null;
  }

  $status = $session->status;
  $completedAt = $session->completedAt->format('Y-m-d H:i:s');
  if ($status != 'not_ready' && isset($_POST['status']) && in_array($_POST['status'], $states)) {
    $status = $_POST['status'];

    if ($status == 'complete') {
      $completedAt = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }
  } else if ($status == 'not_ready' && isset($_POST['action']) && $_POST['action'] == 'send') {
    $status = 'pending';
  }

  $dueDate = $session->dueDate->format('Y-m-d');
  if (isset($_POST['start-date'])) {
    $postDueDate = new DateTime($_POST['due-date'], new DateTimeZone('UTC'));
    $dueDate = $postDueDate->format('Y-m-d');
  }

  $stages = $session->stages;
  foreach ($stages as $key => $value) {
    if (!$stages->$key->completed && !$stages->$key->required_locked) {
      $stages->$key->required = isset($_POST[$key . '-main-check']);
    }
  }

  $updateSession = $db->prepare("UPDATE `onboardingSessions` SET `start` = ?, `charge_outstanding` = ?, `charge_pro_rata` = ?, `welcome_text` = ?, `status` = ?, `due_date` = ?, `completed_at` = ?, `stages` = ? WHERE `id` = ?");
  $updateSession->execute([
    $start,
    (int) $chargeOutstanding,
    (int) $chargeProRata,
    $welcomeText,
    $status,
    $dueDate,
    $completedAt,
    json_encode($stages),
    $id,
  ]);

  // Choose where to go next

  if (isset($_POST['action'])) {
    if ($_POST['action'] == 'fees') {
      // Create a new batch and edit
      $addBatch = $db->prepare("INSERT INTO `membershipBatch` (`ID`, `User`, `StartText`, `Footer`, `DueDate`, `Total`, `PaymentTypes`, `PaymentDetails`, `AutoReminders`, `Creator`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");

      $batchId = Ramsey\Uuid\Uuid::uuid4();

      $addBatch->execute([
        $batchId,
        $session->user,
        null,
        null,
        null,
        0,
        json_encode(['dd', 'card']),
        json_encode([]),
        0,
        app()->user->getId(),
      ]);

      // Set batch ID for session
      $update = $db->prepare("UPDATE onboardingSessions SET batch = ? WHERE id = ?");
      $update->execute([
        $batchId,
        $id,
      ]);

      header('location: ' . autoUrl("memberships/batches/$batchId/edit"));
    } else if ($_POST['action'] == 'send') {
      // Send email
      $session->sendEmail();
      header('location: ' . autoUrl("onboarding/sessions/a/$id"));
    }
  } else {
    // Just save
    header('location: ' . autoUrl("onboarding/sessions/a/$id"));
  }
} catch (Exception $e) {

  header('location: ' . autoUrl("onboarding/sessions/a/$id"));
}
