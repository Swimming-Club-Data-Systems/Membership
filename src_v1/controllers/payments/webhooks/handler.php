<?php

require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;

define("PAYMENT_EMAILS", [
  "Email" => "noreply@" . getenv('EMAIL_DOMAIN'),
  "Name" => app()->tenant->getKey('CLUB_SHORT_NAME') . " Payments"
]);

//$token = "testsecret";

$raw_payload = file_get_contents('php://input');

$headers = getallheaders();
$provided_signature = $headers["Webhook-Signature"];

$calculated_signature = hash_hmac("sha256", $raw_payload, app()->tenant->getKey('GOCARDLESS_WEBHOOK_KEY'));

if ($provided_signature == $calculated_signature) {
  $payload = json_decode($raw_payload, true);

  $db = app()->db;

  // Check if $event["id"] has been done before
  $getCount = $db->prepare("SELECT COUNT(*) FROM paymentWebhookOps WHERE EventID = ?");
  // Add $event["id"] to list to show it's done
  $addEvent = $db->prepare("INSERT INTO paymentWebhookOps (EventID) VALUES (?)");

  // Each webhook may contain multiple events to handle, batched together
  foreach ($payload["events"] as $event) {
    print("Processing event " . $event["id"] . "\n");

    $getCount->execute([$event["id"]]);
    $count = $getCount->fetchColumn();

    if ($count == 0) {

      switch ($event["resource_type"]) {
        case "mandates":
          process_mandate_event($event);
          break;
				case "payments":
          process_payment_event($event);
          break;
        case "payouts":
          process_payout_event($event);
          break;
        default:
          print("Don't know how to process an event with resource_type " . $event["resource_type"] . "\n");
          break;
      }

      $addEvent->execute([$event["id"]]);
    } else {
      echo "Event " . $event["id"] . " already processed\r\n";
    }
  }
  header("HTTP/1.1 200 OK");
} else {
  header("HTTP/1.1 498 Invalid Token");
}

function process_payout_event($event) {
  $db = app()->db;
  $payout = $event["links"]["payout"];
  createOrUpdatePayout($payout, true);
  echo $payout . ' processed';
}

function process_mandate_event($event) {
	
  $db = app()->db;
  $tenant = app()->tenant;
	include BASE_PATH . 'controllers/payments/GoCardlessSetup.php';
  switch ($event["action"]) {
    case "created":
      if (mandateExists($event["links"]["mandate"])) {
        print("Mandate " . $event["links"]["mandate"] . " has been created!\n");
      } else {
        $mandateObject = $client->mandates()->get($event["links"]["mandate"]);
        $customer = $mandateObject->links->customer;
        $query = $db->prepare("SELECT `UserID` FROM `users` WHERE `EmailAddress` = ?");
        $query->execute([($client->customers()->get($customer))->email]);

        if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
          $user = $row['UserID'];

          $mandate = $event["links"]["mandate"];
          $customer = $customer;
          $bankAccount = $mandateObject->links->customer_bank_account;

      	  $bank = $client->customerBankAccounts()->get($bankAccount);
      	  $accHolderName = $bank->account_holder_name;
      	  $accNumEnd = $bank->account_number_ending;
      	  $bankName = $bank->bank_name;

          try {
            $addMandate = $db->prepare("INSERT INTO `paymentMandates` (`UserID`, `Name`, `Mandate`, `Customer`, `BankAccount`, `BankName`, `AccountHolderName`, `AccountNumEnd`, `InUse`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $addMandate->execute([
              $user,
              'Mandate',
              mb_strimwidth($mandate, 0, 20),
              mb_strimwidth($customer, 0, 20),
              mb_strimwidth($bankAccount, 0, 20),
              mb_strimwidth($bankName, 0, 100),
              mb_strimwidth($accHolderName, 0, 30),
              mb_strimwidth($accNumEnd, 0, 2),
              true
            ]);

            $checkDefault = $db->prepare("SELECT COUNT(*) AS ManCount, MandateID FROM `paymentMandates` WHERE `UserID` = ?");
            $checkDefault->execute([$user]);
            $result = $checkDefault->fetch(PDO::FETCH_ASSOC);
            if ($result['ManCount'] == 1) {
              $preferredCount = $db->prepare("SELECT COUNT(*) FROM paymentPreferredMandate WHERE UserID = ?");
              $preferredCount->execute([$user]);

              if ($preferredCount->fetchColumn() == 1) {
                // If no set mandate, add default
                $setDefault = $db->prepare("INSERT INTO `paymentPreferredMandate` (MandateID, UserID) VALUES (?, ?)");
                $setDefault->execute([$result['MandateID'], $user]);
              } else {
                // Else set default
                $setDefault = $db->prepare("UPDATE `paymentPreferredMandate` SET `MandateID` = ? WHERE `UserID` = ?");
                $setDefault->execute([$result['MandateID'], $user]);
              }
            }
          } catch (Exception $e) {
            // Do nothing for now - This functionality is for mandates created
            // via GoCardless
          }

          print("Mandate " . $event["links"]["mandate"] . " has been created! We found the user in question in our system.\n");
        }
      }
      break;
  	case "cancelled":
      print("Mandate " . $event["links"]["mandate"] . " has been cancelled!\n");
			$mandate = $event["links"]["mandate"];

      try {
        $cancelMandate = $db->prepare("UPDATE `paymentMandates` SET `InUse` = ? WHERE `Mandate` = ?");
        $cancelMandate->execute([0, $mandate]);

        $unsetDefault = $db->prepare("SELECT users.UserID, `Forename`, `Surname`, `EmailAddress`, `MandateID`, users.Active FROM `paymentMandates` INNER JOIN `users` ON users.UserID = paymentMandates.UserID WHERE `Mandate` = ? AND Tenant = ?");
        $unsetDefault->execute([
          $mandate,
          $tenant->getId()
        ]);
        $user = $unsetDefault->fetch(PDO::FETCH_ASSOC);
        if ($user != null) {
          $mandateID = $user['MandateID'];
          $unsetDefaultMandate = $db->prepare("DELETE FROM `paymentPreferredMandate` WHERE `MandateID` = ?");
          $unsetDefaultMandate->execute([$mandateID]);

          $getRemainingMandates = $db->prepare("SELECT MandateID FROM paymentMandates WHERE UserID = ? AND InUse = 1");
          $getCountPref = $db->prepare("SELECT COUNT(*) FROM paymentPreferredMandate WHERE UserID = ?");
          $setPrefMandate = $db->prepare("INSERT INTO paymentPreferredMandate (UserID, MandateID) VALUES (?, ?)");

          $getRemainingMandates->execute([
            $user['UserID']
          ]);
          $newPrefMandate = $getRemainingMandates->fetchColumn();
          $countPref = $getCountPref->execute([
            $user['UserID']
          ]);
          if ($newPrefMandate != null && $getCountPref->fetchColumn() == 0) {
            $setPrefMandate->execute([
              $user['UserID'],
              $newPrefMandate
            ]);
          }

          $userID = $user['UserID'];
          
          $getCurrentPref = $db->prepare("SELECT Mandate, BankName, AccountHolderName, AccountNumEnd FROM paymentMandates INNER JOIN paymentPreferredMandate ON paymentPreferredMandate.MandateID = paymentMandates.MandateID WHERE paymentPreferredMandate.UserID = ?");
          $getCurrentPref->execute([
            $user['UserID']
          ]);
          $currentMandate = $getCurrentPref->fetch(PDO::FETCH_ASSOC);

          $oldMandate = $db->prepare("SELECT Mandate, BankName, AccountHolderName, AccountNumEnd FROM paymentMandates WHERE MandateID = ?");
          $oldMandate->execute([
            $user['MandateID']
          ]);
          $oldMandate = $oldMandate->fetch(PDO::FETCH_ASSOC);

          if (bool($user['Active'])) {
            $message = "<h1>Hello " . htmlspecialchars($user['Forename'] . " " . $user['Surname']) . ".</h1>
            <p>Your Direct Debit Mandate for " . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . " has been cancelled.</p>";

            if ($oldMandate != null) {
              $message .= '<p>The cancelled mandate was on ' . htmlspecialchars($oldMandate['AccountHolderName']) . '\'s account with ' . htmlspecialchars(getBankName($oldMandate['BankName'])) . '. Account number ending in &middot;&middot;&middot;&middot;&middot;&middot;' . htmlspecialchars($oldMandate['AccountNumEnd']) . '. Our internal reference for the mandate was ' . htmlspecialchars($oldMandate['Mandate']) . '.</p>';
            }

            if ($currentMandate != null) {
              $message .= '<p>Your current direct debit mandate is set to ' . htmlspecialchars($oldMandate['AccountHolderName']) . '\'s account with ' . htmlspecialchars(getBankName($currentMandate['BankName'])) . '. Account number ending in &middot;&middot;&middot;&middot;&middot;&middot;' . htmlspecialchars($currentMandate['AccountNumEnd']) . '. Our internal reference for this mandate is ' . htmlspecialchars($currentMandate['Mandate']) . '.</p>';
            }

            $message .= "<p>Go to " . htmlspecialchars(autoUrl("")) . " to make any changes to your account and direct debit options.</p>
            <p>Thank you, <br>" . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . "";
            notifySend(
              $user['EmailAddress'],
              "Your Direct Debit Mandate has been Cancelled",
              $message,
              $user['Forename'] . " " . $user['Surname'],
              $user['EmailAddress'],
              PAYMENT_EMAILS
            );
          }
        }

      } catch (Exception $e) { reportError($e); }

      break;
		case "transferred":
			print("Mandate " . $event["links"]["mandate"] . " has been transferred to a new bank!\n");
			$mandate = $event["links"]["mandate"];
			$bankAccount = ($client->mandates()->get($mandate))->links->customer_bank_account;

			$bank = $client->customerBankAccounts()->get($bankAccount);
		  $accHolderName = $bank->account_holder_name;
		  $accNumEnd = $bank->account_number_ending;
		  $bankName = $bank->bank_name;

      $setNewBank = $db->prepare("UPDATE `paymentMandates` SET `BankAccount` = ?, `AccountHolderName` = ?, `AccountNumEnd` = ?, `BankName` = ? WHERE `Mandate` = ?");
      $setNewBank->execute([
        mb_strimwidth($bankAccount, 0, 20),
        mb_strimwidth($accHolderName, 0, 30),
        $accNumEnd,
        mb_strimwidth($bankName, 0, 100),
        mb_strimwidth($mandate, 0, 20)
      ]);

			break;
		case "expired":
			print("Mandate " . $event["links"]["mandate"] . " has expired!\n");
			$mandate = $event["links"]["mandate"];
      $expire = $db->prepare("UPDATE `paymentMandates` SET `InUse` = ? WHERE `Mandate` = ?");
      $expire->execute([false, $mandate]);

			// Get the user ID, set to another bank if possible and let them know.
			$getUser = $db->prepare("SELECT users.UserID, `Forename`, `Surname`, `EmailAddress`,
			`MandateID`, users.Active FROM `paymentMandates` INNER JOIN `users` ON users.UserID =
			paymentMandates.UserID WHERE `Mandate` = ?");
      $getUser->execute([$mandate]);
      $user = $getUser->fetch(PDO::FETCH_ASSOC);
      $mandateID = $user['MandateID'];

			// If default unset
			$unset = $db->prepare("DELETE FROM `paymentPreferredMandate` WHERE `MandateID` = ?");
      $unset->execute([$mandateID]);

			$userID = $user['UserID'];

      $getNextMandate = $db->prepare("SELECT * FROM `paymentMandates` WHERE `UserID` = ? AND `InUse` = ?");
      $getNextMandate->execute([$userID, true]);
			$row = $getNextMandate->fetch(PDO::FETCH_ASSOC);

      if (bool($user['Active'])) {
        if ($row == null) {
          $message = "<h1>Hello " . htmlspecialchars($user['Forename'] . " " . $user['Surname']) . ".</h1>
          <p>Your Direct Debit Mandate for " . app()->tenant->getKey('CLUB_NAME') . " has expired. As this was your only mandate with us, you must set up a new direct debit as soon as possible at " . autoUrl("payments") . ".</p>
          <p>If you have left the club you can ignore this email.</p>
          <p>Thank you, <br>" . app()->tenant->getKey('CLUB_NAME') . "";
          notifySend($user['EmailAddress'], "Your Direct Debit Mandate has
          Expired", $message, $user['Forename'] . " " . $user['Surname'],
          $user['EmailAddress'],PAYMENT_EMAILS);
        } else {
          $mandateID = $row['MandateID'];
          $setNewDefault = $db->prepare("INSERT INTO paymentPreferredMandate (UserID, MandateID) VALUES (?, ?)");
          $setNewDefault->execute([$userID, $mandateID]);
          $message = "<h1>Hello " . $user['Forename'] . " " . $user['Surname'] . ".</h1>
          <p>Your Direct Debit Mandate for " . app()->tenant->getKey('CLUB_NAME') . " has expired. As you had more than one direct debit set up, we've switched your default direct debit to the next available one in our list. You may want to check the details about this before we take any payments from you in order to ensure your're happy with us taking funds from that account.</p>
          <p>Go to " . autoUrl("payments") . " to make any changes.</p>
          <p>Thank you, <br>" . app()->tenant->getKey('CLUB_NAME') . "";
          notifySend($user['EmailAddress'], "Your Direct Debit Mandate has
          Expired", $message, $user['Forename'] . " " . $user['Surname'],
          $user['EmailAddress'], PAYMENT_EMAILS);
        }
      }

			break;
    default:
      print("Don't know how to process a mandate " . $event["action"] . " event\n");
      break;
  }
}

function process_payment_event($event) {
	
  $db = app()->db;
	include BASE_PATH . 'controllers/payments/GoCardlessSetup.php';
  switch ($event["action"]) {
  	case "created":
      print("Payment " . $event["links"]["payment"] . " has been created!\n");
      if (!paymentExists($event["links"]["payment"])) {
        // Create a new Payment
        $date = new DateTime($payment->created_at, new DateTimeZone('UTC'));
        $date = $date->format("Y-m-d");
        $PMkey = $event["links"]["payment"];
        $payment = $client->payments()->get($PMkey);
        $status = $payment->status;
        $amount = $payment->amount;
        $name = $payment->description;
        $currency = $payment->currency;
        $mandate = $payment->links->mandate;
        if (isset($payment->links->payout) && $payment->links->payout != null) {
          createOrUpdatePayout($payout);
        }

        $getMandateAndUser = $db->prepare("SELECT MandateID, UserID FROM paymentMandates WHERE Mandate = ?");
        $getMandateAndUser->execute([$mandate]);
        if ($row = $getMandateAndUser->fetch(PDO::FETCH_ASSOC)) {
          $user = $row['UserID'];
          $mandate = $row['MandateID'];
          $addPayment = $db->prepare("INSERT INTO `payments` (`Date`, `Status`, `UserID`, `MandateID`, `Name`, `Amount`, `Currency`, `PMkey`, `Type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
          $addPayment->execute([
            $date,
            $status,
            $user,
            $mandate,
            mb_strimwidth($name, 0, 50),
            $amount,
            $currency,
            $PMkey,
            'Payment'
          ]);

          $paymentId = $db->lastInsertId();

          // Add item to payments pending
          $getCountFromPP = $db->prepare("SELECT COUNT(*) FROM paymentsPending WHERE Payment = ?");
          $getCountFromPP->execute([
            $paymentId
          ]);

          if ($getCountFromPP->fetchColumn() == 0) {
            $addToPP = $db->prepare("INSERT INTO paymentsPending (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `PMkey`, `Type`, `Payment`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $addToPP->execute([
              $date,
              'Pending',
              $user,
              mb_strimwidth($name, 0, 500),
              $amount,
              $currency,
              $PMkey,
              'Payment',
              $paymentId
            ]);
          }
        }
      }
      break;
		case "customer_approval_granted":
        if (updatePaymentStatus($event["links"]["payment"])) {
  			  print("Payment " . $event["links"]["payment"] . ": Customer Approval has been Granted\n");
        } else {
          print("Failed " . $event["links"]["payment"]);
        }
			break;
		case "customer_approval_denied":
			print("Mandate " . $event["links"]["mandate"] . " has expired!\n");
			break;
    case "submitted":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Submitted\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
		case "confirmed":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Confirmed\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
		case "chargeback_cancelled":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Chargeback Cancelled\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
    case "paid_out":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Paid Out\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
		case "late_failure_settled":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Late Failure Settled\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
		case "chargeback_settled":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Chargeback Settled\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
    case "failed":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Payment Failed\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
    case "charged_back":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Charged Back\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
		case "cancelled":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Cancelled\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
		case "resubmission_requested":
      if (updatePaymentStatus($event["links"]["payment"])) {
        print("Payment " . $event["links"]["payment"] . ": Resubmission Requested\n");
      } else {
        print("Event Failed " . $event["links"]["payment"]);
      }
      break;
    default:
      print("Don't know how to process a payment " . $event["action"] . " event\n");
      break;
  }
}
