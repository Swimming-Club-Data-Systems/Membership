<?php

$db = app()->db;
$tenant = app()->tenant;

use Respect\Validation\Validator as v;

if (!v::intVal()->between(1, 12)->validate((int) $month) || !v::stringType()->length(2, 2)->validate($month)) {
	halt(404);
}

if (!v::intVal()->min(1970, true)->validate((int) $year) || !v::stringType()->length(4, null)->validate($year)) {
	halt(404);
}

$searchDate = $year . "-" . $month . "-" . "%";
$name_type = null;
$title_string = null;

$dateString = date("F Y", strtotime($year . "-" . $month));

$info = null;
if ($type == "squads") {
	$name_type = "SquadFee";
	$info = "Squad";
	$title_string = "Squad Fee payments for " . $dateString;
} else if ($type == "extras") {
	$name_type = "ExtraFee";
	$title_string = "Extra Fee payments for " . $dateString;
} else {
	halt(404);
}

$title = "Squad Fees - " . $dateString;

$sql = $db->prepare("SELECT `users`.`UserID`, `Forename`, `Surname`, `MForename`, `MSurname`,
individualFeeTrack.Amount, individualFeeTrack.Description, payments.Status, payments.PaymentID FROM
(((((`individualFeeTrack` LEFT JOIN `paymentMonths` ON
individualFeeTrack.MonthID = paymentMonths.MonthID) LEFT JOIN `paymentsPending`
ON individualFeeTrack.PaymentID = paymentsPending.PaymentID) LEFT JOIN
`members` ON members.MemberID = individualFeeTrack.MemberID) LEFT JOIN
`payments` ON paymentsPending.PMkey = payments.PMkey) LEFT JOIN `users` ON
users.UserID = individualFeeTrack.UserID) WHERE members.Tenant = ? AND `paymentMonths`.`Date` LIKE
? AND `individualFeeTrack`.`Type` = ? ORDER BY `Forename`
ASC, `Surname` ASC, `users`.`UserID` ASC, `MForename` ASC, `MSurname` ASC;");
$sql->execute([
	$tenant->getId(),
	$searchDate,
	$name_type
]);

$rows = $sql->fetchAll(PDO::FETCH_ASSOC);
$row = null;

if (sizeof($rows) > 0) {
	$row = $rows[0];
}

$user_id = $row['UserID'];
$user_id_last = null;

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=MonthlyFeesExport' . $year . '-' . $month . '.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');
echo "\xef\xbb\xbf";
// output the column headings
fputcsv($output, array($title));
fputcsv($output, array('Parent', 'Swimmer', $info, 'Amount', 'FamilyTotal', 'Paid'));

$paid = [
  'confirmed',
  'paid_out'
];
$not_paid = [
  'cancelled',
  'customer_approval_denied',
  'failed',
  'charged_back'
];
$unconfirmed = [
  'pending_api_request',
  'pending_customer_approval',
  'pending_submission',
  'submitted'
];

for ($i = 0; $i < sizeof($rows); $i++) {
	$name = null;
	$member = null;
	$amount = null;
	$family_total = null;

	if ($row['Forename'] != null && $row['Surname'] != null) {
		if ($user_id_last != $user_id) {
			$name = $row['Forename'] . " " . $row['Surname'];
			$family_total = '£' . number_format(monthlyFeeCost(null, $user_id, "decimal"),2,'.','');
		}
	} else {
		$name = "N/A";
	}

	$member = $row['MForename'] . " " . $row['MSurname'];

	$amount = '£' . (string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2);

  $status_text = "";
  if (in_array($row['Status'], $paid)) {
    $status_text = "Paid";
  } else if (in_array($row['Status'], $not_paid)) {
    $status_text = "No";
  } else if (in_array($row['Status'], $unconfirmed)) {
    $status_text = "Unconfirmed";
  } else {
    //$status_text = "";
  }

	fputcsv($output, array($name, $member, $row['Description'], $amount, $family_total, $status_text));

	if ($i < sizeof($rows)-1) {
		$row = $rows[$i+1];
		$user_id_last = $user_id;
		$user_id = $row['UserID'];
	}
}
