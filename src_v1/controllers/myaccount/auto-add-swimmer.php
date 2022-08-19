<?php

$db = app()->db;
$tenant = app()->tenant;

use Respect\Validation\Validator as v;

// Registration Form Handler

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$asaNumber = trim($asa);
$accessKey = trim($acs);

$searchSQL = $db->prepare("SELECT * FROM members WHERE ASANumber = ? AND AccessKey = ? AND Tenant = ?;");
$searchSQL->execute([
  $asaNumber,
  $accessKey,
  $tenant->getId()
]);

$row = $searchSQL->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

if (!($asaNumber != null && $accessKey != null && v::alnum()->validate($asaNumber) && v::alnum()->validate($accessKey))) {
  halt(404);
}

// Allow addition
$memberID = $row['MemberID'];
$existingUserID = $row['UserID'];

if ($row['UserID'] != null) {
  $sql = $db->prepare("SELECT * FROM users WHERE UserID = ?");
  $sql->execute([
    $existingUserID
  ]);
  $oldUser = $sql->fetch(PDO::FETCH_ASSOC);
  // Warn old parent by email
  $message = "
  <h1>Hello " . htmlspecialchars($oldUser['Forename']) . "</h1>
  <p>Your swimmer, " . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . " has been removed from your account.</p>
  <p>If this was not you, contact <a  href=\"mailto:" . htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) . "\">" . htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) . "</a> as soon as possible</p>";
  notifySend($oldUser['EmailAddress'], $row['MForename'] . " has been
  removed", $message, $oldUser['Forename'] . " " . $oldUser['Surname'],
  $oldUser['EmailAddress']);
}

$accessKey = generateRandomString(6);

// SQL To set UserID foreign key
$sql = $db->prepare("UPDATE `members` SET UserID = ?, AccessKey = ? WHERE MemberID = ?");
$sql->execute([
  $userID,
  $accessKey,
  $memberID
]);

// Get info about swimmer and parent
$sql = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname, users.Forename, users.Surname, users.EmailAddress, members.ASANumber
        FROM (members
          INNER JOIN users ON members.UserID = users.UserID)
          WHERE users.UserID = ? AND members.MemberID = ?;");
$sql->execute([
  $userID,
  $memberID
]);
$row = $sql->fetch(PDO::FETCH_ASSOC);

// Notify new parent with email
$message = "
<p>Hello " . htmlspecialchars($row['Forename'] . " " . $row['Surname']) . ",</p>
<p>Your swimmer, " . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . " has been registered
with your account.</p>
<ul>
  <li>" . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . "</li>
  <li>Swim England Number: " .htmlspecialchars($row['ASANumber']) . "</li>
  <li>" . htmlspecialchars(app()->tenant->getKey('CLUB_SHORT_NAME')) . " Member ID: " . htmlspecialchars($row['MemberID']) . "</li>
</ul>
<p>If this was not you, contact <a href=\"mailto:"  . htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) . "\">
"  . htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) . "</a> as soon as possible</p>";
notifySend($row['EmailAddress'], "You've added " . $row['MForename'] . "
to your account", $message, $row['Forename'] . " " . $row['Surname'],
$row['EmailAddress']);

$_SESSION['TENANT-' . app()->tenant->getId()]['AddSwimmerSuccessState'] = "
<div class=\"alert alert-success\">
<p class=\"mb-0\"><strong>We were able to successfully add your swimmer</strong></p>
<p>We've sent an email confirming this to you.</p>
<p class=\"mb-0\"><a href=\"" . autoUrl("my-account/addswimmer") . "\"
class=\"alert-link\">Add another</a> or <a href=\"" . autoUrl("my-account") . "\"
class=\"alert-link\">return to My Account</a></p>
</div>";
header("Location: " . autoUrl("swimmers/" . $memberID));