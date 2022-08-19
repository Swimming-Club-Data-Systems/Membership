<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

/**
 * Exports of monthly financial reports
 * Shows payments by group and outgoings (GoCardless fees on payouts)
 *
 * This means the system covers gross and net
 *
 * TODO: Extend to cover Stripe card payments
 */

$db = app()->db;
$tenant = app()->tenant;

$searchDate = $year . "-" . $month . "-" . "%";
$getPayments = $db->prepare("SELECT * FROM (SELECT 'StripeDD' AS Provider, 'Payments' AS Type, paymentsPending.Amount, paymentsPending.Type AS DebitCredit, CONCAT(users.Forename, ' ', users.Surname) AS `User`, paymentsPending.Name, MetadataJSON AS Info, payments.Status AS `Status`, payments.stripeFailureCode AS `StripeFailureCode`, paymentsPending.Date AS `Date`, payments.StripeFee AS `Fees`, payments.stripePaymentIntent AS `ID`, payments.StripeFee AS TransFee, paymentCategories.Name AS CatName, paymentCategories.Description AS CatDesc, paymentCategories.ID AS CatID FROM (((`paymentsPending` INNER JOIN `payments` ON paymentsPending.Payment = payments.PaymentID) INNER JOIN `users` ON users.UserID = payments.UserID) LEFT JOIN paymentCategories ON paymentCategories.ID = paymentsPending.Category) WHERE payments.stripePaymentIntent IS NOT NULL AND payments.Date LIKE :searchDate AND users.Tenant = :tenant UNION ALL SELECT 'GoCardless' AS Provider, 'Payouts' AS Type, Amount, NULL AS DebitCredit, NULL AS `User`, NULL AS `Name`, NULL AS Info, NULL AS `Status`, NULL AS `StripeFailureCode`, ArrivalDate AS `Date`, Fees, NULL AS `ID`, NULL AS TransFee, NULL AS CatName, NULL AS CatDesc, NULL AS CatID FROM paymentsPayouts WHERE paymentsPayouts.ArrivalDate LIKE :searchDate AND paymentsPayouts.Tenant = :tenant UNION ALL SELECT 'GoCardless' AS Provider, 'Payments' AS Type, paymentsPending.Amount, paymentsPending.Type AS DebitCredit, CONCAT(users.Forename, ' ', users.Surname) AS `User`, paymentsPending.Name, MetadataJSON AS Info, payments.Status AS `Status`, NULL AS `StripeFailureCode`, paymentsPending.Date AS `Date`, NULL AS `Fees`, NULL AS `ID`, NULL AS TransFee, paymentCategories.Name AS CatName, paymentCategories.Description AS CatDesc, paymentCategories.ID AS CatID FROM ((((`paymentsPending` INNER JOIN `payments` ON paymentsPending.Payment = payments.PaymentID) INNER JOIN `users` ON users.UserID = payments.UserID) LEFT JOIN paymentsPayouts ON paymentsPayouts.ID = payments.Payout) LEFT JOIN paymentCategories ON paymentCategories.ID = paymentsPending.Category) WHERE payments.Date LIKE :searchDate AND users.Tenant = :tenant UNION ALL SELECT 'Stripe' AS Provider, 'Payments' AS Type, stripePaymentItems.Amount, 'Payment' AS DebitCredit, CONCAT(users.Forename, ' ', users.Surname) AS `User`, CONCAT(stripePaymentItems.Name, ' ', stripePaymentItems.Description) AS `Name`, NULL AS Info, stripePayments.Paid AS `Status`, NULL AS `StripeFailureCode`, stripePayments.DateTime AS `Date`, stripePaymentItems.AmountRefunded AS `Fees`, stripePayments.Intent AS `ID`, stripePayments.Fees AS TransFee, paymentCategories.Name AS CatName, paymentCategories.Description AS CatDesc, paymentCategories.ID AS CatID FROM (((`stripePaymentItems` INNER JOIN `stripePayments` ON stripePaymentItems.Payment = stripePayments.ID) INNER JOIN `users` ON users.UserID = stripePayments.User) LEFT JOIN paymentCategories ON paymentCategories.ID = stripePaymentItems.Category) WHERE stripePayments.DateTime LIKE :searchDate AND users.Tenant = :tenant AND stripePayments.Paid UNION ALL SELECT 'Stripe' AS Provider, 'Payouts' AS Type, Amount, NULL AS DebitCredit, NULL AS `User`, ID AS `Name`, NULL AS Info, NULL AS `Status`, NULL AS `StripeFailureCode`, ArrivalDate AS `Date`, NULL AS `Fees`, NULL AS `ID`, NULL AS TransFee, NULL AS CatName, NULL AS CatDesc, NULL AS CatID FROM stripePayouts WHERE ArrivalDate LIKE :searchDate AND stripePayouts.Tenant = :tenant) AS UnitedTable ORDER BY UnitedTable.Date ASC, UnitedTable.User ASC");
$getPayments->execute([
  'searchDate' => $searchDate,
  'tenant' => $tenant->getId()
]);

$checkStripeStatus = $db->prepare("SELECT `Reason`, `Status` FROM `stripeDisputes` WHERE `PaymentIntent` = ?");

// pre($getPayments->fetchAll(PDO::FETCH_ASSOC)); /*

// output headers so that the file is downloaded rather than displayed
//header('Content-Disposition: attachment; filename=SCDSMembership-FinanceReport.csv');

$now = new DateTime('now', new DateTimeZone('UTC'));

$array = [];
$types = [];

$doneStripeCardFees = [];

// output the column headings
//$array += ['about' => app()->tenant->getKey('CLUB_NAME') . ' Finance Report'];
while ($row = $getPayments->fetch(PDO::FETCH_ASSOC)) {
  if ($row['Type'] == 'Payments') {
    $date = new DateTime($row['Date']);
    $in = $out = 0;
    if ($row['Provider'] == 'GoCardless' || $row['Provider'] == 'StripeDD') {
      if ($row['DebitCredit'] == 'Payment') {
        $in = $row['Amount'];
      } else if ($row['DebitCredit'] == 'Refund') {
        $out = $row['Amount'];
      }
    } else if ($row['Provider'] == 'Stripe' && $row['DebitCredit'] == 'Payment') {
      $in = $row['Amount'];
    }
    $json = json_decode($row['Info']);
    $name = $row['Name'];
    $infoText = '';
    if (isset($json->PaymentType) && $json->PaymentType == 'SquadFees' && isset($json->Members)) {
      foreach ($json->Members as $member) {
        $infoText .= '£' . $member->Fee . ' (' . $member->FeeName . ', ' . $member->MemberName . ') ';
      }
    }
    $details = $row['User'] . ', ' . $name . ' ' . $infoText;
    $status = 'Unknown';
    if ($row['Provider'] == 'GoCardless' || $row['Provider'] == 'StripeDD') {
      $status = paymentStatusString($row['Status'], $row['StripeFailureCode']);
    } else if ($row['Provider'] == 'Stripe') {
      $status = 'Expect OK';

      $checkStripeStatus->execute([
        $row['ID'],
      ]);
      if ($specialStatus = $checkStripeStatus->fetch(PDO::FETCH_ASSOC)) {
        // A problem
        $status = 'Please Check (' . $specialStatus['Status'] . ', ' . $specialStatus['Reason'] . ')';
      }
    }
    $grouping = null;
    if (isset($json->type)) {
      $grouping = [
        'object' => $json->type->object,
        'id' => $json->type->id,
        'name' => $json->type->name
      ];
      if (!isset($types[$json->type->object])) {
        $types += [$json->type->object => []];
      }
      if (!isset($types[$json->type->object][$json->type->id])) {
        $types[$json->type->object] += [$json->type->id => $json->type->name];
      }
    }

    if ($row['CatID']) {
      $grouping = [
        'object' => 'CustomCategory',
        'id' => $row['CatID'],
        'name' => $row['CatName']
      ];
      if (!isset($types['CustomCategory'])) {
        $types += ['CustomCategory' => []];
      }
      if (!isset($types['CustomCategory'][$row['CatID']])) {
        $types['CustomCategory'] += [$row['CatID'] => $row['CatName']];
      }
    }

    $in = (int) $in;
    $out = (int) $out;

    if ($in < 0) {
      $out += abs($in);
      $in = 0;
    }

    if (($row['Provider'] == 'Stripe' || $row['Provider'] == 'StripeDD') && !in_array($row['ID'], $doneStripeCardFees)) {
      $item = [
        'object' => 'Payment',
        'type' => 'Transaction Fee',
        'date' => $date->format("Y-m-d"),
        'details' => 'Fee for ' . $row['ID'],
        'credits' => 0,
        'debits' => (int) $row['TransFee'],
        'income' => 'Net',
        'status' => 'N/A',
        'grouping' => null
      ];

      $array[] = $item;
      $doneStripeCardFees[] = $row['ID'];
    }

    $item = [
      'object' => 'Payment',
      'type' => $row['DebitCredit'],
      'date' => $date->format("Y-m-d"),
      'details' => trim($details),
      'credits' => (int) $in,
      'debits' => (int) $out,
      'income' => 'Gross',
      'status' => $status,
      'grouping' => $grouping
    ];
    $array[] = $item;
  } else if ($row['Type'] == 'Payouts') {
    $dateString = null;
    $status = 'Pending';
    if ($row['Date'] != null) {
      $date = new DateTime($row['Date']);
      $dateString = $date->format("Y-m-d");
      $status = 'Paid out';
    }
    if ($row['Provider'] == 'GoCardless') {
      $details = "GoCardless bank payout";
      $in = $row['Amount'];
      $out = $row['Fees'];
      $item = [
        'object' => 'Payout',
        'date' => $dateString,
        'details' => $details . ' before fees',
        'credits' => ((int) $in) + ((int) $out),
        'debits' => 0,
        'income' => 'Gross',
        'status' => $status
      ];
      $array[] = $item;
      $item = [
        'object' => 'Payout',
        'date' => $dateString,
        'details' => $details . ' - Fees',
        'credits' => 0,
        'debits' => (int) $out,
        'income' => 'Net',
        'status' => $status
      ];
      $array[] = $item;
      $item = [
        'object' => 'Payout',
        'date' => $dateString,
        'details' => $details . ' - Total banked',
        'credits' => (int) $in,
        'debits' => 0,
        'income' => 'Net',
        'status' => $status
      ];
      $array[] = $item;
    } else if ($row['Provider'] == 'Stripe') {
      $item = [
        'object' => 'Payout',
        'date' => $dateString,
        'details' => $row['Name'] . ' - Total banked',
        'credits' => (int) $row['Amount'],
        'debits' => 0,
        'income' => 'Net',
        'status' => 'Expect OK'
      ];
      $array[] = $item;
    }
  }
  //fputcsv($output, $row);
}

$output = [
  'about' => app()->tenant->getKey('CLUB_NAME') . ' Finance Report',
  'producer' => 'Swimming Club Data Systems - Membership Software',
  'month' => $month,
  'year' => $year,
  'date_produced' => $now->format("Y-m-d"),
  'types' => $types,
  'items' => $array,
];
