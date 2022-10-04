<?php

use function GuzzleHttp\json_encode;

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$json = [];

http_response_code(200);
header('content-type: application/json');

try {

  if (isset($_POST['submission'])) {

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

    $date = new DateTime('now', new DateTimeZone('UTC'));

    // Add new null record
    $add = $db->prepare("INSERT INTO `covidHealthScreen` (`ID`, `DateTime`, `Member`, `OfficerApproval`, `ApprovedBy`, `Document`) VALUES (?, ?, ? ,?, ?, ?)");
    $add->execute([
      Ramsey\Uuid\Uuid::uuid4()->toString(),
      $date->format('Y-m-d H:i:s'),
      $id,
      (int) false,
      $user->getId(),
      null,
    ]);

    $json = [
      'status' => 200,
    ];

    // Send an email to the member's parent/account
    $getUser = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
    $getUser->execute([
      $member['UserID'],
    ]);

    $memberUser = $getUser->fetch(PDO::FETCH_ASSOC);

    if ($memberUser) {
      $subject = 'We need a new COVID-19 Health Survey for ' . $member['MForename'];

      $subTime = new DateTime($row['DateTime'], new DateTimeZone('UTC'));
      $subTime->setTimezone(new DateTimeZone('Europe/London'));

      $message = '<p>Hello ' . htmlspecialchars($memberUser['Forename'] . ' ' . $memberUser['Surname']) . '</p>';

      $message .= '<p>We have voided ' . htmlspecialchars($member['MForename'] . ' ' . $member['MSurname']) . '\'s COVID-19 Health Survey which was submitted at ' . htmlspecialchars($subTime->format('H:i, j F Y')) . '.</p>';

      $message .= '<p>We might have done this because of one of the following:</p><ul>';

      $message .= '<li>If you\'ve been out of the area for a while,</li>';
      $message .= '<li>You or somebody you\'re in close contact with contracted or was suspected of having COVID-19,</li>';
      $message .= '<li>Any other reason, which for reasons of confidentiality, does not have to be explained by ' . htmlspecialchars($tenant->getName()) . '.</li>';

      $message .= '</ul><p>As a result, we will need you to <a href="' . htmlspecialchars(autoUrl('covid/health-screening')) . '" target="_blank">complete a new COVID-19 Health Survey</a> before we can let you back into our training sessions.</p>';

      $message .= '<p>If you have received this email, you may also be sent an email asking you to complete a new COVID-19 Risk Awareness Form.</p>';

      $message .= '<p>Thank you for your support at this time, <br>The ' . htmlspecialchars($tenant->getName()) . ' team.</p>';

      notifySend(null, $subject, $message, $memberUser['Forename'] . ' ' . $memberUser['Surname'], $memberUser['EmailAddress']);
    }
  } else if (isset($_POST['squad'])) {

    // Get the squad
    $getSquad = $db->prepare("SELECT `SquadID` FROM squads WHERE SquadID = ? AND Tenant = ?");
    $getSquad->execute([
      $_POST['squad'],
      $tenant->getId(),
    ]);
    $squad = $getSquad->fetch(PDO::FETCH_ASSOC);

    if (!$squad) {
      throw new Exception('Not found');
    }

    $getCountRep = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE Squad = ? AND User = ?");
    $getCountRep->execute([
      $_POST['squad'],
      $user->getId(),
    ]);
    $rep = $getCountRep->fetchColumn() > 0;

    if (!$rep && !$user->hasPermission('Admin') && !$user->hasPermission('Coach') && !$user->hasPermission('Galas')) {
      if ($member['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
        throw new Exception('Not found');
      }
    }

    // Get members
    $getMembers = $db->prepare("SELECT Member FROM squadMembers WHERE Squad = ?");
    $getMembers->execute([
      $_POST['squad'],
    ]);

    $date = new DateTime('now', new DateTimeZone('UTC'));

    // Add new null record
    $add = $db->prepare("INSERT INTO `covidHealthScreen` (`ID`, `DateTime`, `Member`, `OfficerApproval`, `ApprovedBy`, `Document`) VALUES (?, ?, ? ,?, ?, ?)");

    while ($member = $getMembers->fetchColumn()) {
      $add->execute([
        Ramsey\Uuid\Uuid::uuid4()->toString(),
        $date->format('Y-m-d H:i:s'),
        $member,
        (int) false,
        $user->getId(),
        null,
      ]);
    }

    $json = [
      'status' => 200,
    ];
  }
} catch (Exception $e) {
  $message = $e->getMessage();
  if (get_class($e) == 'PDOException') {
    $message = 'A database error occurred.';
    reportError($e);
  }

  $json = [
    'status' => 403,
    'error' => $message,
  ];
}

echo json_encode($json);
