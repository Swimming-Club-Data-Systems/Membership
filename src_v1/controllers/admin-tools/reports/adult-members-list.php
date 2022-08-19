<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  $today = new DateTime('now', new DateTimeZone('Europe/London'));
  $today->setTime(0, 0, 0, 0);

  $targetDate = new DateTime($_GET['date'], new DateTimeZone('Europe/London'));

  $target = clone $targetDate;
  $target->sub(new DateInterval('P18Y'));

  if ($targetDate < $today) {
    throw new Exception('Invalid date');
  }

  // Get members
  $getMembers = $db->prepare("SELECT MForename, MSurname, ASANumber, DateOfBirth, MemberID, Forename, Surname, members.UserID FROM members LEFT JOIN users ON users.UserID = members.UserID WHERE members.Tenant = ? AND DateOfBirth <= ? ORDER BY MSurname ASC, MForename ASC");
  $getMembers->execute([
    $tenant->getId(),
    $target->format('Y-m-d'),
  ]);

  header("content-type: text/csv");
  header("Content-Disposition: attachment; filename=\"members_over_18.csv\"");

  $fp = fopen('php://output', 'w');

  fputcsv($fp, [
    'Copyright ' . $today->format('Y') . ' Swimming Club Data Systems',
  ]);

  fputcsv($fp, [
    '',
  ]);

  fputcsv($fp, [
    'Report generated for ' . app()->user->getFullName() . ', ' . $tenant->getName(),
  ]);

  fputcsv($fp, [
    '',
  ]);

  fputcsv($fp, [
    'Only publish data to the left of the --GDPR BORDER-- column',
  ]);

  fputcsv($fp, [
    '',
  ]);

  fputcsv($fp, [
    'Surname',
    'Forename',
    'ASA Number',
    'Age',
    '--GDPR BORDER--',
    'Date of Birth',
    'Member ID',
    'User ID',
    'User Surname',
    'User Forename',
  ]);

  while ($member = $getMembers->fetch(PDO::FETCH_OBJ)) {

    $dob = new DateTime($member->DateOfBirth, new DateTimeZone('Europe/London'));
    $age = $today->diff($dob);

    fputcsv($fp, [
      $member->MSurname,
      $member->MForename,
      $member->ASANumber,
      $age->format('%y'),
      '------',
      $member->DateOfBirth,
      $member->MemberID,
      $member->UserID,
      $member->Surname,
      $member->Forename,
    ]);
  }

  fclose($fp);
} catch (Exception $e) {
  pre($e);
  // header("location: " . autoUrl('admin/reports/adult-members'));
  // return;
}
