<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

if (!$session->batch) {
  // Complete
  $session->completeTask('fees');
} else {

  $db = app()->db;

  $getBatch = $db->prepare("SELECT membershipBatch.ID id, membershipBatch.Completed completed, DueDate due, Total total, PaymentTypes payMethods, PaymentDetails payDetails FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.User WHERE membershipBatch.ID = ? AND users.Tenant = ?");
  $getBatch->execute([
    $session->batch,
    app()->tenant->getId(),
  ]);

  $batch = $getBatch->fetch(PDO::FETCH_OBJ);

  if ($batch->total > 0) {

    if (!isset($_POST['pay-method'])) {
      // halt(404);
      throw new Exception();
    }

    try {
      $batchPay = \SCDS\Memberships\Batch::goToCheckout($session->batch, $_POST['pay-method']);

      if ($batchPay->type == 'checkout') {
        $checkoutSession = $batchPay->checkoutSession;

        $checkoutSession->metadata['return']['url'] = autoUrl('onboarding/go/fees/success?checkout_session=' . urlencode($checkoutSession->id));
        $checkoutSession->metadata['return']['instant'] = false;
        $checkoutSession->metadata['return']['buttonString'] = 'Return to fee information page';

        $checkoutSession->metadata['cancel']['url'] = autoUrl('onboarding/go/start-task');

        $checkoutSession->save();

        http_response_code(302);
        header("Location: " . $checkoutSession->getUrl());
      } else if ($batchPay->type == 'dd') {

        $paymentInfo = json_encode([
          'type' => 'direct_debit',
          'data' => []
        ]);

        // \SCDS\Memberships\Batch::completeBatch($session->batch, $paymentInfo);
        \SCDS\Memberships\Batch::completeDirectDebitBatch($session->batch);

        // This already gets called
        // $session->completeTask('fees');

        $_SESSION['PaymentSuccess'] = true;

        http_response_code(302);
        header("Location: " . autoUrl('onboarding/go'));
      } else {
        http_response_code(302);
        header("Location: " . autoUrl('onboarding/go/start-task'));
      }
    } catch (Exception $e) {
      // halt(404);
      throw $e;
    }
  } else {
    // Nothing to pay

    // Complete the batch
    $paymentInfo = json_encode([
      'type' => 'no_payment',
      'data' => []
    ]);

    \SCDS\Memberships\Batch::completeBatch($session->batch, $paymentInfo);

    $session->completeTask('fees');

    $_SESSION['PaymentSuccess'] = true;

    http_response_code(302);
    header("Location: " . autoUrl('onboarding/go'));
  }
}
