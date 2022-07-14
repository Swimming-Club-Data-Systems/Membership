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

    $getSubmission = $db->prepare("SELECT `Member`, `DateTime`, `Gala` FROM covidGalaHealthScreen INNER JOIN members ON covidGalaHealthScreen.Member = members.MemberID WHERE covidGalaHealthScreen.ID = ? AND members.Tenant = ?");
    $getSubmission->execute([
      $_POST['submission'],
      $tenant->getId(),
    ]);
    $row = $getSubmission->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      throw new Exception('Not found');
    }

    $id = $row['Member'];
    $gala = $row['Gala'];

    $getMember = $db->prepare("SELECT MemberID, UserID, MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
    $getMember->execute([
      $id,
      $tenant->getId(),
    ]);

    $member = $getMember->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
      throw new Exception('Not found');
    }

    $date = new DateTime('now', new DateTimeZone('UTC'));

    // Add new null record
    $add = $db->prepare("INSERT INTO `covidGalaHealthScreen` (`ID`, `DateTime`, `Member`, `MemberAgreement`, `Guardian`, `GuardianAgreement`, `Gala`) VALUES (?, ?, ? ,?, ?, ?, ?)");
    $add->execute([
      Ramsey\Uuid\Uuid::uuid4()->toString(),
      $date->format('Y-m-d H:i:s'),
      $id,
      (int) false,
      null,
      null,
      $gala,
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
      $subject = 'We need a new COVID-19 Return to Competition Form for ' . $member['MForename'];

      $galaName = $db->prepare("SELECT GalaName FROM galas WHERE GalaID = ?");
      $galaName->execute([
        $gala
      ]);
      $galaName = $galaName->fetchColumn();

      $subTime = new DateTime($row['DateTime'], new DateTimeZone('UTC'));
      $subTime->setTimezone(new DateTimeZone('Europe/London'));

      $message = '<p>Hello ' . htmlspecialchars($memberUser['Forename'] . ' ' . $memberUser['Surname']) . '</p>';

      $message .= '<p>We have voided ' . htmlspecialchars($member['MForename'] . ' ' . $member['MSurname']) . '\'s COVID-19 Return to Competition for ' . htmlspecialchars($galaName) . ' which was submitted at ' . htmlspecialchars($subTime->format('H:i, j F Y')) . '.</p>';

      $message .= '<p>We might have done this because of one of the following:</p><ul>';

      $message .= '<li>If you\'ve been out of the area for a while,</li>';
      $message .= '<li>You or somebody you\'re in close contact with contracted or was suspected of having COVID-19,</li>';
      $message .= '<li>Any other reason, which for reasons of confidentiality, does not have to be explained by ' . htmlspecialchars($tenant->getName()) . '.</li>';

      $message .= '</ul><p>As a result, we will need you to <a href="' . htmlspecialchars(autoUrl('covid/competition-health-screening')) . '" target="_blank">complete a new COVID-19 (' . htmlspecialchars($galaName) . ') Return to Competition Form</a> before we can let you compete.</p>';

      $message .= '<p>If you have received this email, you may also be sent an email asking you to complete a new COVID-19 Health Survey or COVID-19 Risk Awareness Form.</p>';

      $message .= '<p>Thank you for your support at this time, <br>The ' . htmlspecialchars($tenant->getName()) . ' team.</p>';

      notifySend(null, $subject, $message, $memberUser['Forename'] . ' ' . $memberUser['Surname'], $memberUser['EmailAddress']);
    }
  } else if (isset($_POST['gala'])) {

    // Get the gala
    $getGala = $db->prepare("SELECT `GalaID` FROM galas WHERE GalaID = ? AND Tenant = ?");
    $getGala->execute([
      $_POST['gala'],
      $tenant->getId(),
    ]);
    $gala = $getGala->fetch(PDO::FETCH_ASSOC);

    if (!$gala) {
      throw new Exception('Not found');
    }

    $time = new DateTime('now', new DateTimeZone('UTC'));
    if ($_POST['action'] == 'voidOutdated') {
      $time->sub(new DateInterval('P7D'));
    }

    // Get members
    $getMembers = $db->prepare("SELECT `Member` FROM `covidGalaHealthScreen` WHERE `Gala` = ? AND `DateTime` <= ?");
    $getMembers->execute([
      $_POST['gala'],
      $time->format('Y-m-d H:i:s'),
    ]);

    $date = new DateTime('now', new DateTimeZone('UTC'));

    // Add new null record
    $add = $db->prepare("INSERT INTO `covidGalaHealthScreen` (`ID`, `DateTime`, `Member`, `MemberAgreement`, `Guardian`, `GuardianAgreement`, `Gala`) VALUES (?, ?, ? ,?, ?, ?, ?)");

    while ($member = $getMembers->fetchColumn()) {
      $add->execute([
        Ramsey\Uuid\Uuid::uuid4()->toString(),
        $date->format('Y-m-d H:i:s'),
        $member,
        (int) false,
        null,
        null,
        $_POST['gala'],
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
