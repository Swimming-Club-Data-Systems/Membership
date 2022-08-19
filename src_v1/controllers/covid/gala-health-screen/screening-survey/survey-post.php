<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!isset($_POST['member']) || !isset($_POST['gala'])) halt(404);

$id = $_POST['member'];

$date = new DateTime('now', new DateTimeZone('Europe/London'));

$getMember = $db->prepare("SELECT MemberID, UserID, MForename, MSurname, DateOfBirth FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $id,
  $tenant->getId(),
]);

$member = $getMember->fetch(PDO::FETCH_ASSOC);

if (!$member) {
  halt(404);
}

$getGala = $db->prepare("SELECT GalaName FROM galas WHERE GalaID = ? AND GalaDate >= ? AND Tenant = ?");
$getGala->execute([
  $_POST['gala'],
  $date->format('Y-m-d'),
  $tenant->getId(),
]);

$gala = $getGala->fetch(PDO::FETCH_ASSOC);

if (!$gala) {
  halt(404);
}

$getCountRep = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad IN (SELECT Squad FROM squadReps WHERE User = ?)");
$getCountRep->execute([
  $id,
  $user->getId(),
]);
$rep = $getCountRep->fetchColumn() > 0;

if (!$rep && !$user->hasPermission('Admin') && !$user->hasPermission('Coach') && !$user->hasPermission('Galas')) {
  if ($member['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
    halt(404);
  }
}

$getParent = $db->prepare("SELECT Forename, Surname FROM users WHERE UserID = ?");
$getParent->execute([
  $member['UserID'],
]);

$parent = $getParent->fetch(PDO::FETCH_ASSOC);

$today = new DateTime('now', new DateTimeZone('Europe/London'));
$age = new DateTime($member['DateOfBirth'], new DateTimeZone('Europe/London'));

$age = $age->diff($today);
$age = (int) $age->format('%y');

$addData = null;

http_response_code(302);

try {

  if (!isset($_POST['member-declaration'])) {
    throw new Exception('No member declaration given.');
  }

  if (app()->tenant->getKey('ASA_CLUB_CODE') == 'UOSZ' && !isset($_POST['uosswpc-member-declaration'])) {
    throw new Exception('No member declaration given to UoSSWPC agreement.');
  }

  $date = new DateTime('now', new DateTimeZone('UTC'));
  $today = new DateTime('now', new DateTimeZone('Europe/London'));
  $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();

  $dob = new DateTime($member['DateOfBirth'], new DateTimeZone('Europe/London'));

  $age = $dob->diff($today);
  $age = (int) $age->format('%y');

  $memberAgreement = (int) bool($_POST['member-declaration']);

  $guardian = null;
  $guardianAgreement = null;
  if ($age < 18) {

    if (!isset($_POST['parent-declaration'])) {
      throw new Exception('No parent/guardian declaration given.');
    }

    $guardian = $member['UserID'];
    $guardianAgreement = (int) bool($_POST['parent-declaration']);

    if ($guardian == null) {
      throw new Exception('User requires approval by a parent/guardian, yet no guardian is on file.');
    }
  }

  // Add to database
  $add = $db->prepare("INSERT INTO `covidGalaHealthScreen` (`ID`, `DateTime`, `Member`, `MemberAgreement`, `Guardian`, `GuardianAgreement`, `Gala`) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $add->execute([
    $uuid,
    $date->format('Y-m-d H:i:s'),
    $id,
    $memberAgreement,
    $guardian,
    $guardianAgreement,
    $_POST['gala']
  ]);

  // Get details
  $getMember = $db->prepare("SELECT MForename, MSurname, Forename, Surname, EmailAddress FROM members INNER JOIN users ON members.UserID = users.UserID WHERE MemberID = ?");
  $getMember->execute([
    $id,
  ]);
  $member = $getMember->fetch(PDO::FETCH_ASSOC);

  if ($member) {
    $expires = clone $date;
    $expires->add(new DateInterval('P7D'));

    $subject = 'Return to competition declaration received';

    $message = '<p>Hello ' . htmlspecialchars($member['Forename'] . ' ' . $member['Surname']) . '</p>';

    $message .= '<p>We have received your signed COVID-19 Return to Competition form for ' . htmlspecialchars($member['MForename'] . ' ' . $member['MSurname']) . ' for ' . htmlspecialchars($gala['GalaName']) . '. The form was submitted at ' . htmlspecialchars($date->format('H:i:s, j F Y')) . '.</p>';

    $message .= '<p>Please take a lateral flow test within the two days before the start of the competition and <a href="https://www.gov.uk/report-covid19-result" target="_blank">report the result to the NHS</a>. You can <a href="https://www.nhs.uk/conditions/coronavirus-covid-19/testing/regular-rapid-coronavirus-tests-if-you-do-not-have-symptoms/" target="_blank">order rapid lateral flow coronavirus (COVID-19) tests from the NHS</a> for free.</p>';

    $message .= '<p>This declaration will expire seven days after submission at ' . htmlspecialchars($expires->format('H:i:s, j F Y')) . '. If the gala spans multiple weekends, you must resubmit the form no more than seven days ahead of each weekend. </p>';

    $message .= '<p>For further details of how we process your personal data or your child’s personal data please view our <a href="' . htmlspecialchars(autoUrl('privacy')) . '" target="_blank">Privacy Policy</a>.</p>';

    $message .= '<p>Thank you for your support at this time,</p><p>The ' . htmlspecialchars($tenant->getName()) . ' team.</p>';

    notifySend(null, $subject, $message, $member['Forename'] . ' ' . $member['Surname'], $member['EmailAddress']);
  }

  $_SESSION['CovidGalaSuccess'] = true;
  header('location: ' . autoUrl('covid/competition-health-screening'));
} catch (PDOException $e) {
  // throw new Exception('A database error occurred');
  $_SESSION['CovidGalaError'] = 'A database error occurred';
  header('location: ' . autoUrl('competition-health-screening/new-survey?member=' . $_POST['member'] . '?gala=' . $_POST['gala']));
} catch (Exception $e) {
  $_SESSION['CovidGalaError'] = $e->getMessage();
  header('location: ' . autoUrl('competition-health-screening/new-survey?member=' . $_POST['member'] . '?gala=' . $_POST['gala']));
}
