<?php

ignore_user_abort(true);
set_time_limit(0);

try {

  $db = app()->db;
  $tenant = app()->tenant;
  require BASE_PATH . 'controllers/payments/GoCardlessSetupClient.php';


  $sendEmail = $db->prepare("INSERT INTO notify (UserID, Status, Subject, Message, ForceSend, EmailType) VALUES (:user, 'Queued', :subject, :message, 0, 'Payments')");

  $hasMandateQuery = null;
  try {
    $sql = "SELECT * FROM `paymentMandates` INNER JOIN `paymentPreferredMandate` ON paymentMandates.UserID = paymentPreferredMandate.UserID WHERE paymentMandates.UserID = ? AND `InUse` = '1'";
    $hasMandateQuery = $db->prepare($sql);
  } catch (Exception $e) {
    reportError($e);
    throw new Exception();
  }

  $date = date("Y-m") . "-01";
  $day = date("d");

  $updatePayments = $db->prepare("UPDATE `payments` SET `Status` = ?, `MandateID` = ?, `PMkey` = ? WHERE `PaymentID` = ?");
  $updatePaymentsPending = $db->prepare("UPDATE `paymentsPending` SET `Status` = ?, `PMkey` = ? WHERE Payment = ?");

  try {
    $getPayments = $db->prepare("SELECT payments.UserID, Amount, Currency, `Name`, PaymentID FROM ((payments INNER JOIN users ON payments.UserID = users.UserID) LEFT JOIN paymentSchedule ON payments.UserID = paymentSchedule.UserID) WHERE users.Tenant = ? AND ((Status = 'pending_api_request' AND `Day` <= ? AND Type = 'Payment') OR (Status = 'pending_api_request' AND `Day` IS NULL AND `Type` = 'Payment')) LIMIT 4");
    $getPayments->execute([
      $tenant->getId(),
      $day
    ]);
    while ($row = $getPayments->fetch(PDO::FETCH_ASSOC)) {
      $userid = $row['UserID'];
      try {
        $hasMandateQuery->execute([$userid]);
      } catch (Exception $e) {
        // reportError($e);
        throw new Exception();
      }
      $mandateInfo = $hasMandateQuery->fetch(PDO::FETCH_ASSOC);

      $email_statment_id;

      if ($mandateInfo && $row['Amount'] > 100) {
        $amount = $row['Amount'];
        $currency = $row['Currency'];
        $description = $row['Name'];
        $idemKey = "PaymentID-" . $row['PaymentID'];
        $mandate = $mandateInfo['Mandate'];
        $mandateid = $mandateInfo['MandateID'];

        // MAKE AN API CALL TO GC
        try {
          $payment = $client->payments()->create([
            "params" => [
              "amount" => $amount,
              "currency" => $currency,
              "description" => $description,
              "links" => [
                "mandate" => $mandate
              ],
            ],
            "headers" => [
              "Idempotency-Key" => $idemKey
            ]
          ]);

          $paymentID = $row['PaymentID'];
          $id = $payment->id;
          $email_statment_id = $id;
          $status = $payment->status;

          $updatePayments->execute([
            $status,
            $mandateid,
            $id,
            $paymentID
          ]);

          $updatePaymentsPending->execute([
            'Requested',
            $id,
            $paymentID
          ]);
        } catch (\GoCardlessPro\Core\Exception\ApiException $e) {
          if ($e->getType() == 'invalid_state' || $e->getType() == 'invalid_api_usage') {
            $paymentID = $row['PaymentID'];
            $id = "CASH-DDFAIL" . $paymentID;
            $email_statment_id = $id;

            $updatePayments->execute([
              'cust_not_dd',
              null,
              $id,
              $paymentID
            ]);

            $updatePaymentsPending->execute([
              'Paid',
              $id,
              $paymentID
            ]);

            // reportError($e);
          } else {
            throw $e;
          }
        } catch (Exception $e) {
          throw $e;
        }
      } else {
        try {
          $paymentID = $row['PaymentID'];
          $id = "CASH" . $paymentID;
          $email_statment_id = $id;

          $updatePayments->execute([
            'cust_not_dd',
            null,
            $id,
            $paymentID
          ]);

          $updatePaymentsPending->execute([
            'Paid',
            $id,
            $paymentID
          ]);
        } catch (Exception $e) {
          // reportError($e);
          throw new Exception();
        }
      }

      $message_subject = "Your bill for " . date("F Y") . " is now available";
      $message_content = '';
      if (!$mandateInfo) {
        $message_content .= '<table style="margin: 0 0 1rem 0; padding: 1rem; border-radius: 0.25rem; border: 1px solid rgba(0, 0, 0, 0.125);background-color: #f8f9fa;"><tr><td><p><strong>Warning: You do not have a direct debit mandate set up with us.</strong>';
        if (app()->tenant->isCLS()) {
          $message_content .= ' If you are paying us manually, a £3 surcharge applies to your monthly fee. Login to <a href="' . autoUrl("payments") . '">the Membership System</a> to set up a direct debit before next month\'s bill.</p>';
          $message_content .= '<p class="mb-0" style="margin-bottom: 0;">If you expected to pay us by direct debit this month, please contact the treasurer as soon as possible - Your swimmers could be suspended if you fail to pay squad fees.';
        }
        $message_content .= '</p></td></tr></table>';
      }
      $message_content .= '<p>Your bill for ' . date("F Y") . ' is now available. The total amount payable for this month is <strong>&pound;' . number_format(($row['Amount'] / 100), 2, '.', ',') . '</strong>.</p>';

      $message_content .= '<p>You can <a href="' . autoUrl("payments/statements/" . $paymentID) . '">view a full itemised statement for this payment online</a> or <a href="' . autoUrl("payments/statements/" . $paymentID . "/pdf") . '">download your full statement as a PDF</a>. Statements show each item you have been charged or credited for.</p>';

      $message_content .= '<p>Squad fees were calculated using the squad your swimmers were members of on 1 ' . date("F Y") . '.</p><hr>';

      if ($mandateInfo) {
        $message_content .= '<p>You will receive an email from our direct debit service provider GoCardless within the next three working days confirming the amount to be charged by direct debit.</p>';
      } else {
        $message_content .= '<p><strong>You have not set up a direct debit.</strong> We recommend that you do so urgently.</p>';
      }

      $email_info = [
        "user" => $row['UserID'],
        "subject" => $message_subject,
        "message" => $message_content
      ];

      if ($row['Amount'] > 0) {
        $sendEmail->execute($email_info);
      }
    }
  } catch (Exception $e) {
    // Report error by halting
    // reportError($e);

    throw $e;
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
