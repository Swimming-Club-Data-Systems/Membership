<?php

$db = app()->db;
$tenant = app()->tenant;

ignore_user_abort(true);
set_time_limit(0);

use \Stripe\Exception\ApiConnectionException;

try {

  \Stripe\Stripe::setApiKey(getenv('STRIPE'));
  \Stripe\Stripe::setMaxNetworkRetries(3);

  $sendEmail = $db->prepare("INSERT INTO notify (UserID, Status, Subject, Message, ForceSend, EmailType) VALUES (:user, 'Queued', :subject, :message, 0, 'Payments')");

  $hasMandateQuery = $db->prepare("SELECT stripeMandates.Mandate, stripeMandates.ID, stripeMandates.Customer, stripeMandates.Reference, stripeMandates.Last4, stripeMandates.SortCode FROM `stripeMandates` INNER JOIN `stripeCustomers` ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND stripeMandates.MandateStatus = 'active'");

  $date = date("Y-m") . "-01";
  $day = date("d");

  $today = new DateTime('now', new DateTimeZone('Europe/London'));

  $updatePayments = $db->prepare("UPDATE `payments` SET `Status` = ?, `stripeMandate` = ?, `stripePaymentIntent` = ? WHERE `PaymentID` = ?");
  $updatePaymentsPending = $db->prepare("UPDATE `paymentsPending` SET `Status` = ? WHERE Payment = ?");

  try {
    $getPayments = $db->prepare("SELECT payments.UserID, Amount, Currency, `Name`, PaymentID FROM ((payments INNER JOIN users ON payments.UserID = users.UserID) LEFT JOIN paymentSchedule ON payments.UserID = paymentSchedule.UserID) WHERE users.Tenant = ? AND payments.Date <= ? AND ((Status = 'pending_api_request' AND `Day` <= ? AND Type = 'Payment') OR (Status = 'pending_api_request' AND `Day` IS NULL AND `Type` = 'Payment')) LIMIT 4");
    $getPayments->execute([
      $tenant->getId(),
      $today->format('Y-m-d'),
      $day,
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
        $currency = mb_strtolower($row['Currency']);
        $description = $row['Name'];
        $idemKey = "PaymentID-" . $row['PaymentID'];
        $mandate = $mandateInfo['Mandate'];
        $mandateid = $mandateInfo['ID'];
        $paymentMethod = $mandateInfo['ID'];
        $customer = $mandateInfo['Customer'];

        // MAKE AN API CALL TO STRIPE WITH THE PAYMENT METHOD
        try {
          $intent = \Stripe\PaymentIntent::create([
            'payment_method_types' => ['bacs_debit'],
            'payment_method' => $paymentMethod,
            'customer' => $customer,
            'confirm' => true,
            'amount' => $row['Amount'],
            'currency' => $currency,
            'description' => $description,
            'metadata' => [
              'payment_category' => 'monthly_fee',
              'user_id' => $userid,
              'payment_id' => $row['PaymentID'],
            ]
          ], [
            'stripe_account' => $tenant->getStripeAccount(),
            'idempotency_key' => $idemKey,
          ]);

          $paymentID = $row['PaymentID'];
          $id = $intent->id;
          $email_statment_id = $paymentID;
          $status = $intent->status;

          $updatePayments->execute([
            $status,
            $mandateid,
            $id,
            $paymentID
          ]);

          $updatePaymentsPending->execute([
            'Requested',
            $paymentID
          ]);
        } catch (\Stripe\Error\InvalidRequest | \Stripe\Error\Authentication | \Stripe\Error\Api | Stripe\Exception\CardException $e) {
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
            $paymentID
          ]);

          reportError($e);
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
            $paymentID
          ]);
        } catch (Exception $e) {
          // reportError($e);
          throw new Exception();
        }
      }

      $message_subject = "Your statement for " . date("F Y") . " is now available";
      $message_content = '';
      if (!$mandateInfo) {
        $message_content .= '<table style="margin: 0 0 1rem 0; padding: 1rem; border-radius: 0.25rem; border: 1px solid rgba(0, 0, 0, 0.125);background-color: #f8f9fa;"><tr><td><p><strong>Warning: You do not have a direct debit mandate set up with us.</strong>';
        if (app()->tenant->isCLS()) {
          $message_content .= ' If you are paying us manually, a £3 surcharge applies to your monthly fee. Login to <a href="' . autoUrl("payments") . '">the Membership System</a> to set up a direct debit before next month\'s bill.</p>';
          $message_content .= '<p class="mb-0" style="margin-bottom: 0;">If you expected to pay us by direct debit this month, please contact the treasurer as soon as possible - Your swimmers could be suspended if you fail to pay squad fees.';
        }
        $message_content .= '</p></td></tr></table>';
      }
      $message_content .= '<p>Your bill for ' . date("F Y") . ' is now available. The total amount payable for this month is <strong>&pound;' . (string) \Brick\Math\BigDecimal::of((string) $row['Amount'])->withPointMovedLeft(2)->toScale(2) . '</strong>.</p>';

      $message_content .= '<p>You can <a href="' . autoUrl("payments/statements/" . $paymentID) . '">view a full itemised statement for this payment online</a>. Statements show each item you have been charged or credited for.</p>';

      $message_content .= '<p>Squad fees were calculated using the squads your members were in as of on 1 ' . date("F Y") . '.</p><hr>';

      if ($mandateInfo) {
        $message_content .= '<p>You will receive an email from our payment service provider within the next three working days confirming the amount to be charged by direct debit.</p>';
      } else {
        $message_content .= '<p><strong>You have not set up a direct debit.</strong> We recommend that you do so.</p>';
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

  reportError($e);

  header('content-type: application/json');
  echo (json_encode([
    'status' => 500,
    'error' => [
      $e->getLine(),
      $e->getMessage(),
    ],
  ]));
}
