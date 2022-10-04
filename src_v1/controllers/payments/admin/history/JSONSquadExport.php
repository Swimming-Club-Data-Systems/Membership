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

if ($type == "squads") {
	$name_type = "SquadFee";
	$title_string = "Squad Fee payments for " . $dateString;
} else if ($type == "extras") {
	$name_type = "ExtraFee";
	$title_string = "Extra Fee payments for " . $dateString;
} else {
	halt(404);
}

$title = "Squad Fees - " . $dateString;

$sql = "SELECT `users`.`UserID`, `Forename`, `Surname`, `MForename`, `MSurname`,
individualFeeTrack.Amount, individualFeeTrack.Description, payments.Status,
payments.PaymentID FROM (((((`individualFeeTrack` LEFT JOIN
`paymentMonths` ON individualFeeTrack.MonthID = paymentMonths.MonthID) LEFT JOIN
`paymentsPending` ON individualFeeTrack.PaymentID = paymentsPending.PaymentID)
LEFT JOIN `members` ON members.MemberID = individualFeeTrack.MemberID) LEFT JOIN
`payments` ON paymentsPending.PMkey = payments.PMkey) LEFT JOIN `users` ON
users.UserID = individualFeeTrack.UserID) WHERE `paymentMonths`.`Date` LIKE
? AND `individualFeeTrack`.`Type` = ? AND members.Tenant = ? ORDER BY `Forename`
ASC, `Surname` ASC, `users`.`UserID` ASC, `MForename` ASC, `MSurname` ASC;";

$query = $db->prepare($sql);
$query->execute([
	$searchDate,
	$name_type,
	$tenant->getId()
]);

$row = $query->fetch(PDO::FETCH_ASSOC);

$user_id = $row['UserID'];
$user_id_last = null;
$while = true;

$export_array = [
  'Type'		=>	$name_type,
  'Date'		=> [
    'Year'		=> $year,
    'Month'		=> $month
  ],
  'Parents'	=> []
];

// output headers so that the file is downloaded rather than displayed
header('Content-Type: application/json; charset=UTF-8');
//header('Content-Disposition: attachment; filename=MonthlyFeesExport' . $year . '-' . $month . '.csv');

$parents = [];
$parent = [];
$swimmers = [];

if ($row != null) {
  do {
  	$name = null;
  	$member = null;
  	$amount = null;
  	$family_total = null;

  	if ($row['UserID'] != null) {
  		if ($user_id_last != $user_id) {
  			$parent['Name'] = \SCDS\Formatting\Names::format($row['Forename'], $row['Surname']);
  			$parent['FamilyTotal'] = monthlyFeeCost(null, $user_id, "int");
  			$parent['FamilyTotalString'] = '£' . number_format(monthlyFeeCost(null, $user_id, "decimal"),2,'.','');
  			$parent['PaymentStatus'] = $row['Status'];
  			$parent['PaymentStatusString'] = paymentStatusString($row['Status']);
  		}
  	} else {
  		$parent['Name'] = null;
  		$parent['FamilyTotal'] = (int) $row['Amount'];
  		$parent['FamilyTotalString'] = '£' . (string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2);
  		$parent['PaymentStatusString'] = "No Parent or Direct Debit Available";
  	}

  	$swimmer = [
  		'Name'				=> \SCDS\Formatting\Names::format($row['MForename'], $row['MSurname']),
  		'Description'	=> $row['Description'],
  		'Amount'			=> (int) $row['Amount'],
  		'AmountString'=> '£' . (string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2),
  		'ClubPaysFees'=> false
  	];

  	$swimmers[] = $swimmer;
  	$swimmer = [];

  	if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
  		$user_id_last = $user_id;
  		$user_id = $row['UserID'];

  		if ($user_id != $user_id_last || $user_id_last == null) {
  			$parent['Swimmers'] = $swimmers;
  			$export_array['Parents'][] = $parent;
  			$parent = [];
  			$swimmers = [];
  		}
  	} else {
      $while = false;

      $parent['Swimmers'] = $swimmers;
      $export_array['Parents'][] = $parent;
      $parent = [];
      $swimmers = [];
    }
  } while ($while);
}

echo json_encode($export_array, JSON_PRETTY_PRINT);
