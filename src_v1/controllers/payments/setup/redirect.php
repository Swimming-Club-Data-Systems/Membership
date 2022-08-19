<?php

$db = app()->db;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$url_path = "payments";
if ($renewal_trap) {
  $url_path = "renewal/payments";
}

$selectSchedule = $db->prepare("SELECT COUNT(*) FROM `paymentSchedule` WHERE `UserID` = ?");
$selectSchedule->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

if ($selectSchedule->fetchColumn() == 0) {
  header("Location: " . autoUrl("payments/setup/0"));
} else {
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
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
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
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
    ]);

    $insertToMandates = $db->prepare("INSERT INTO `paymentMandates` (`UserID`, `Name`, `Mandate`, `Customer`, `BankAccount`, `BankName`, `AccountHolderName`, `AccountNumEnd`, `InUse`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertToMandates->execute([
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
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
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
      $mandateDbId
    ]);

    $db->commit();

    $_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] = 'success';
    header("Location: " . autoUrl($url_path . "/setup/4"));
  } catch (\GoCardlessPro\Core\Exception\ApiException | \GoCardlessPro\Core\Exception\MalformedResponseException $e) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] = $e->getType();
    $db->rollBack();
    header("Location: " . autoUrl($url_path . "/setup/4"));
  } catch (\GoCardlessPro\Core\Exception\ApiConnectionException $e) {
    $db->rollBack();
    reportError($e);
    
    $_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] = $e->getType();
    header("Location: " . autoUrl($url_path . "/setup/4"));
  } catch (Exception $e) {
    $db->rollBack();
    reportError($e);
    halt(500);
  }
}
