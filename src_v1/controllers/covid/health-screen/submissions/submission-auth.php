<?php

use function GuzzleHttp\json_encode;

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$json = [];

http_response_code(200);
header('content-type: application/json');

try {

  $getSubmission = $db->prepare("SELECT `Member`, `DateTime` FROM covidHealthScreen INNER JOIN members ON covidHealthScreen.Member = members.MemberID WHERE covidHealthScreen.ID = ? AND members.Tenant = ?");
  $getSubmission->execute([
    $_POST['submission'],
    $tenant->getId(),
  ]);
  $row = $getSubmission->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    throw new Exception('Not found');
  }

  $id = $row['Member'];

  $getMember = $db->prepare("SELECT MemberID, UserID, MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
  $getMember->execute([
    $id,
    $tenant->getId(),
  ]);

  $member = $getMember->fetch(PDO::FETCH_ASSOC);

  if (!$member) {
    throw new Exception('Not found');
  }

  $getCountRep = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad IN (SELECT Squad FROM squadReps WHERE User = ?)");
  $getCountRep->execute([
    $id,
    $user->getId(),
  ]);
  $rep = $getCountRep->fetchColumn() > 0;

  if (!$rep && !$user->hasPermission('Admin') && !$user->hasPermission('Coach') && !$user->hasPermission('Galas')) {
    if ($member['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
      throw new Exception('Not found');
    }
  }

  // Approve or deny
  $update = $db->prepare("UPDATE covidHealthScreen SET OfficerApproval = ?, ApprovedBy = ? WHERE ID = ?");
  $update->execute([
    (int) ($_POST['type'] == 'approve'),
    $user->getId(),
    $_POST['submission']
  ]);

  $json = [
    'status' => 200,
  ];

  // Send an email to the user's parent
  $getUser = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
  $getUser->execute([
    $member['UserID'],
  ]);

  $memberUser = $getUser->fetch(PDO::FETCH_ASSOC);

  if ($memberUser) {
    $type = 'REJECTED';
    if ($_POST['type'] == 'approve') {
      $type = 'APPROVED';
    }
    $subject = 'We\'ve ' . $type . ' ' . $member['MForename'] . '\'s COVID-19 Health Survey';

    $subTime = new DateTime($row['DateTime'], new DateTimeZone('UTC'));
    $subTime->setTimezone(new DateTimeZone('Europe/London'));

    $message = '<p>Hello ' . htmlspecialchars($memberUser['Forename'] . ' ' . $memberUser['Surname']) . '</p>';

    $message .= '<p>' . htmlspecialchars($user->getFullName()) . ' has <strong>' . htmlspecialchars($type) . '</strong> ' . htmlspecialchars($member['MForename'] . ' ' . $member['MSurname']) . '\'s COVID-19 Health Screening Survey which was submitted at ' . htmlspecialchars($subTime->format('H:i, j F Y')) . '.</p>';

    if ($_POST['type'] != 'approve') {
      $message .= '<p><strong>' . htmlspecialchars($member['MForename'] . ' ' . $member['MSurname']) . ' can not train until we have approved a COVID-19 Health Screening Survey.</strong></p>';

      $message .= '<p>If you believe that your health survey has been rejected in error, please contact the club and ask for use to check your survey again. It may be the case that you need to submit a new survey at a later date.</p>';
    } else {
      $message .= '<p>You\'re now allowed to attend club sessions until we ask you to complete another survey.</p>';
    }

    $message .= '<p>Thank you for your support at this time, <br>The ' . htmlspecialchars($tenant->getName()) . ' team.</p>';

    notifySend(null, $subject, $message, $memberUser['Forename'] . ' ' . $memberUser['Surname'], $memberUser['EmailAddress']);
  }

} catch (PDOException $e) {
  throw new Exception('A database error occurred');
} catch (Exception $e) {
  reportError($e);

  $json = [
    'status' => 403,
    'error' => $e->getMessage(),
  ];
}

echo json_encode($json);
