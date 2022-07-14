<?php

ignore_user_abort(true);
set_time_limit(0);

try {
  require BASE_PATH . 'controllers/payments/GoCardlessSetupClient.php';

  $db = app()->db;
  $tenant = app()->tenant;

  $date = new DateTime('now', new DateTimeZone('Europe/London'));

  $query = $db->prepare("SELECT PMKey, paymentRetries.UserID FROM paymentRetries INNER JOIN users ON users.UserID = paymentRetries.UserID INNER JOIN payments ON payments.PMkey = paymentRetries.PMKey WHERE `Tenant` = ? AND `Day` <= ? AND `Tried` = ? LIMIT 4");
  $query->execute([
    $tenant->getId(),
    $date->format("Y-m-d"),
    false
  ]);

  $mark_tried = $db->prepare("UPDATE paymentRetries SET Tried = ? WHERE PMKey = ?");
  $email = $db->prepare("INSERT INTO notify (UserID, Status, Subject, Message, ForceSend, EmailType) VALUES (?, ?, ?, ?, ?, ?)");

  $retries = $query->fetchAll();

  foreach ($retries as $retry) {
    try {
      $client->payments()->retry($retry['PMKey']);
      $mark_tried->execute([true, $retry['PMKey']]);

      $subject = 'Payment Retry (' . $retry['PMKey'] . ')';
      $message = '
      <p>We\'re now retrying payment ' . $retry['PMKey'] . '. The money should leave your account within the next few days, if enough money is available.</p>';
      $message .= '<p>Kind regards,<br>The ' . app()->tenant->getKey('CLUB_NAME') . ' Team</p>';
      $email->execute([$retry['UserID'], 'Queued', $subject, $message, 1, 'Payments']);

    } catch (Exception $e) {
      reportError($e);
      $mark_tried->execute([true, $retry['PMKey']]);

      $subject = 'Payment Retry Failed';
      $message = '
      <p>We attempted to retry payment ' . $retry['PMKey'] . ' but an error occured before we were able to complete the retry request.</p>
      <p>Please contact the treasurer as soon as possible.</p>';
      $message .= '<p>Kind regards,<br>The ' . app()->tenant->getKey('CLUB_NAME') . ' Team</p>';
      $email->execute([$retry['UserID'], 'Queued', $subject, $message, 1, 'Payments']);
    }
  }

  header("content-type: application/json");
  http_response_code(200);
  echo json_encode([
    'status' => 200,
  ]);

} catch (Exception $e) {
  header('content-type: application/json');
  echo (json_encode([
    'status' => 500,
    'error' => [
      $e->getLine(),
      $e->getMessage(),
    ],
  ]));
}