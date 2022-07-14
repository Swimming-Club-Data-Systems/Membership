<?php

$user = app()->user;
$db = app()->db;

$getBatch = $db->prepare("SELECT membershipBatch.ID id, membershipBatch.Completed completed, DueDate due, Total total, PaymentTypes payMethods, PaymentDetails payDetails, users.UserID user, users.Forename firstName, users.Surname lastName FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.User WHERE membershipBatch.ID = ? AND users.Tenant = ?");
$getBatch->execute([
  $id,
  app()->tenant->getId(),
]);

$batch = $getBatch->fetch(PDO::FETCH_OBJ);

if (!$batch) halt(404);

if ($batch->user != $user->getId() && !$user->hasPermission('Admin')) halt(404);

if (!isset($_POST['pay-method'])) {
  halt(404);
}

try {
  $batchPay = \SCDS\Memberships\Batch::goToCheckout($id, $_POST['pay-method']);

  if ($batchPay->type == 'checkout') {
    $checkoutSession = $batchPay->checkoutSession;

    $checkoutSession->metadata['return']['url'] = autoUrl('memberships');
    $checkoutSession->metadata['return']['instant'] = false;
    $checkoutSession->metadata['return']['buttonString'] = 'Return to batch information page';

    $checkoutSession->metadata['cancel']['url'] = autoUrl('memberships/batches/' . $id);

    $checkoutSession->save();

    http_response_code(302);
    header("Location: " . $checkoutSession->getUrl());
  } else if ($batchPay->type == 'dd') {

    \SCDS\Memberships\Batch::completeBatch($id, json_encode([
      'type' => 'direct_debit',
      'data' => []
    ]));

    http_response_code(302);
    header("Location: " . autoUrl('memberships/batches/' . $id));
  }
} catch (Exception $e) {
  reportError($e);
  halt(404);
}