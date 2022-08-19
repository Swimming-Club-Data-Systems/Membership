<?php

$db = app()->db;
$tenant = app()->tenant;

$getInfo = $db->prepare("SELECT members.UserID `uid`, Forename ufn, Surname usn, members.MemberID mid, MForename mfn, MSurname msn, members.ASANumber asa, `clubMembershipClasses`.`Name` cat, DateOfBirth dob, Mandate, BankName, AccountHolderName, AccountNumEnd FROM ((((members LEFT JOIN users ON members.UserID = users.UserID) LEFT JOIN paymentPreferredMandate ON users.UserID = paymentPreferredMandate.UserID) LEFT JOIN paymentMandates ON paymentPreferredMandate.MandateID = paymentMandates.MandateID) LEFT JOIN clubMembershipClasses ON members.NGBCategory = clubMembershipClasses.ID) WHERE members.Active AND members.Tenant = ? ORDER BY usn ASC, ufn ASC, users.UserID ASC, msn ASC, mfn ASC");
$getInfo->execute([
  $tenant->getId()
]);

$getSquads = $db->prepare("SELECT SquadName, SquadFee FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE squadMembers.Member = ?");

$logins = $db->prepare("SELECT `Time`, `IPAddress`, Browser, `Platform`, `GeoLocation` FROM userLogins WHERE UserID = ? ORDER BY `Time` DESC LIMIT 1");

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=membership-data-export.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, [
  'User forename',
  'User surname',
  'Member forename',
  'Member surname',
  $tenant->getKey('NGB_NAME') . ' number',
  $tenant->getKey('NGB_NAME') . ' category',
  'Date of birth',
  'Squad',
  'Squad fee',
  'Bank',
  'Bank a/c holder',
  'Bank a/c num end',
  'Last login',
]);
while ($info = $getInfo->fetch(PDO::FETCH_ASSOC)) {
  $logins->execute([
    $info['uid']
  ]);
  $loginInfo = $logins->fetch(PDO::FETCH_ASSOC);
  $lastLogin = 'Never';
  if ($loginInfo) {
    $time = new DateTime($loginInfo['Time'], new DateTimeZone('UTC'));
    $time->setTimezone(new DateTimeZone('Europe/London'));
    $lastLogin = $time->format('H:i T \o\n d/m/y');
  }
  $dob = new DateTime($info['dob']);

  // Squads
  $getSquads->execute([
    $info['mid'],
  ]);
  $squads = '';
  $fees = '';
  $doneFirst = false;
  while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
    if ($doneFirst) {
      $squads .= ', ';
      $fees .= ', ';
    }
    $doneFirst = true;

    $squads .= $squad['SquadName'];
    $fee = '£0.00';
    try {
      $fee = '£' . (string) \Brick\Math\BigDecimal::of((string) $squad['SquadFee'])->toScale(2);
    } catch (Exception $e) { }
    $fees .= $fee;
  }

  fputcsv($output, [
    $info['ufn'],
    $info['usn'],
    $info['mfn'],
    $info['msn'],
    $info['asa'],
    $info['cat'],
    $dob->format("d/m/Y"),
    $squads,
    $fees,
    getBankName($info['BankName']),
    $info['AccountHolderName'],
    $info['AccountNumEnd'],
    $lastLogin,
  ]);
}