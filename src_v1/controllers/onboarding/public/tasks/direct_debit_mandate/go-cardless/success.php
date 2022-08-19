<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(503);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$db = app()->db;

http_response_code(303);

// if ($intent->status == 'succeeded') {
//   $session->completeTask('direct_debit_mandate');

//   $_SESSION['SetupMandateSuccess'] = [
//     'SortCode' => $intent->payment_method->bacs_debit->sort_code,
//     'Last4' => $intent->payment_method->bacs_debit->last4,
//   ];

//   header('location: ' . autoUrl('onboarding/go'));
// } else {
//   header('location: ' . autoUrl('onboarding/go/start-task'));
// }

// GC Code

$client = null;
try {
  $client = SCDS\GoCardless\Client::get();
} catch (Exception $e) {
  halt(404);
}

try {
  $db->beginTransaction();

  $redirectFlowId = $_REQUEST['redirect_flow_id'];
  $redirectFlow = $client->redirectFlows()->complete(
    $redirectFlowId, //The redirect flow ID from above.
    ["params" => ["session_token" => $_SESSION['TENANT-' . app()->tenant->getId()]['Token']]]
  );

  $mandate = $redirectFlow->links->mandate;
  $customer = $redirectFlow->links->customer;
  $bankAccount = $redirectFlow->links->customer_bank_account;

  $bank = $client->customerBankAccounts()->get($bankAccount);
  $accHolderName = $bank->account_holder_name;
  $accNumEnd = $bank->account_number_ending;
  $bankName = $bank->bank_name;

  // So all good so far, disable any old direct debits and cancel
  $getMandates = $db->prepare("SELECT Mandate, MandateID FROM paymentMandates WHERE UserID = ? AND InUse = 1");
  $getMandates->execute([
    $user->getId()
  ]);
  $setOutOfUse = $db->prepare("UPDATE paymentMandates SET InUse = 0 WHERE MandateID = ?");
  while ($oldMandate = $getMandates->fetch(PDO::FETCH_ASSOC)) {
    try {
      // Cancel the mandate
      $client->mandates()->cancel($oldMandate['Mandate']);

      // Only set OOU in DB if above does not throw exception
      $setOutOfUse->execute([
        $oldMandate['MandateID']
      ]);
    } catch (Exception $e) {
      // Returns cancellation_failed error on failure
      // Oops can't cancel
    }
  }

  // Delete old preferred mandates if existing
  $deletePref = $db->prepare("DELETE FROM paymentPreferredMandate WHERE UserID = ?");
  $deletePref->execute([
    $user->getId()
  ]);

  $insertToMandates = $db->prepare("INSERT INTO `paymentMandates` (`UserID`, `Name`, `Mandate`, `Customer`, `BankAccount`, `BankName`, `AccountHolderName`, `AccountNumEnd`, `InUse`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $insertToMandates->execute([
    $user->getId(),
    'Mandate',
    $mandate,
    $customer,
    $bankAccount,
    $bankName,
    $accHolderName,
    $accNumEnd,
    true
  ]);

  $mandateDbId = $db->lastInsertId();

  // Set as preferred mandate
  $setPref = $db->prepare("INSERT INTO paymentPreferredMandate (UserID, MandateID) VALUES (?, ?)");
  $setPref->execute([
    $user->getId(),
    $mandateDbId
  ]);

  $db->commit();

  $session->completeTask('direct_debit_mandate');

  $_SESSION['SetupMandateSuccess'] = [
    'SortCode' => $bankName,
    'Last4' => '······' . $accNumEnd,
  ];
  header('location: ' . autoUrl('onboarding/go'));
} catch (\GoCardlessPro\Core\Exception\ApiException | \GoCardlessPro\Core\Exception\MalformedResponseException $e) {
  $db->rollBack();
  header('location: ' . autoUrl('onboarding/go/start-task'));
} catch (\GoCardlessPro\Core\Exception\ApiConnectionException $e) {
  $db->rollBack();
  reportError($e);

  header('location: ' . autoUrl('onboarding/go/start-task'));
} catch (Exception $e) {
  $db->rollBack();
  reportError($e);
  header('location: ' . autoUrl('onboarding/go/start-task'));
}
