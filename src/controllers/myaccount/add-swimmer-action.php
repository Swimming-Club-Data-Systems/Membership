<?php

use Respect\Validation\Validator as v;
global $db;

// Registration Form Handler

$userID = $_SESSION['UserID'];
$asaNumber = trim(($_POST['asa']));
$accessKey = trim($_POST['accessKey']);

$isTemporaryNumber = false;
if (mb_stripos($asaNumber, env('ASA_CLUB_CODE'))) {
  $asaNumber = str_replace(env('ASA_CLUB_CODE'), '', $asaNumber);
}

$getSwimmer = $db->prepare("SELECT MemberID, SquadID, UserID FROM members WHERE ASANumber = ? AND AccessKey = ? LIMIT 1");
if ($isTemporaryNumber) {
  $getSwimmer = $db->prepare("SELECT MemberID, SquadID, UserID FROM members WHERE MemberID = ? AND AccessKey = ? LIMIT 1");
}
$getSwimmer->execute([$asaNumber, $accessKey]);
$row = $getSwimmer->fetch(PDO::FETCH_ASSOC);

if ($asaNumber != null && $accessKey != null && v::alnum()->validate($asaNumber) && v::alnum()->validate($accessKey)) {
  if ($row != null) {
    // Allow addition
    $memberID = $row['MemberID'];
    $squadID = $row['SquadID'];
    $existingUserID = $row['UserID'];

    if ($row['UserID'] != null) {
      $getCurrentUser = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
      $getCurrentUser->execute([$existingUserID]);
      $oldUser = $getCurrentUser->fetch(PDO::FETCH_ASSOC);
      // Warn old parent by email
      $message = "
      <h1>Hello " . htmlspecialchars($oldUser['Forename']) . "</h1>
      <p>Your swimmer, " . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . " has been removed
      from your account.</p>
      <p>If this was not you, contact <a href=\"mailto:" . htmlspecialchars(env('CLUB_EMAIL')) . "\">" . htmlspecialchars(env('CLUB_EMAIL')) . "</a> as soon as possible</p>";
      notifySend("", "Swimmer removed from your account", $message,
      $oldUser['Forename'] . " " . $oldUser['Surname'],
      $oldUser['EmailAddress']);
    }

    $accessKey = generateRandomString(6);

    // SQL To set UserID foreign key
    $updateSwimmer = $db->prepare("UPDATE members SET UserID = ?, AccessKey = ? WHERE MemberID = ?");
    $updateSwimmer->execute([$_SESSION['UserID'], $accessKey, $memberID]);

    // Get info about swimmer and parent
    $sql = "SELECT members.MemberID, members.MForename, members.MSurname, users.Forename, users.Surname, users.EmailAddress, members.ASANumber, squads.SquadName, squads.SquadFee
            FROM ((members
              INNER JOIN users ON members.UserID = users.UserID)
              INNER JOIN squads ON members.SquadID = squads.SquadID
            ) WHERE members.MemberID = ?";
    $getInfo = $db->prepare($sql);
    $getInfo->execute([$memberID]);
    $row = $getInfo->fetch(PDO::FETCH_ASSOC);

    // Notify new parent with email
    $message = "
    <p>Hello " . htmlspecialchars($row['Forename']) . ",</p>
    <p>Your swimmer, " . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . " has been registered
    with your account.</p>
    <ul>
      <li>" . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . "</li>
      <li>Squad: " . htmlspecialchars($row['SquadName']) . "</li>
      <li>Monthly Fee: &pound;" . number_format((int) $row['SquadFee'], 2, '.', ',') . "</li>
      <li>Swim England Number: " . htmlspecialchars($row['ASANumber']) . "</li>
      <li>" . htmlspecialchars(CLUB_SHORT_NAME) . " Member ID: " . htmlspecialchars($row['MemberID']) . "</li>
    </ul>
    <p>If this was not you, contact <a href=\"mailto:"  . htmlspecialchars(CLUB_EMAIL) . "\">
    "  . htmlspecialchars(CLUB_EMAIL) . "</a> as soon as possible</p>";
    notifySend($row['EmailAddress'], "You've added " . $row['MForename'] . " to your account",
    $message, $row['Forename'] . " " . $row['Surname'],
    $row['EmailAddress']);

    $_SESSION['AddSwimmerSuccessState'] = "
    <div class=\"alert alert-success\">
    <p class=\"mb-0\"><strong>We were able to successfully add your swimmer</strong></p>
    <p>We've sent an email confirming this to you.</p>
    <p class=\"mb-0\"><a href=\"" . autoUrl("my-account/addswimmer") . "\"
    class=\"alert-link\">Add another</a> or <a href=\"" . autoUrl("my-account") . "\"
    class=\"alert-link\">return to My Account</a></p>
    </div>";

    // Return to My Account
    header("Location: " . autoUrl("my-account/addswimmer"));

  }
  else {
    // Error, too many records found - Database error
    $_SESSION['ErrorState'] = "
    <div class=\"alert alert-danger\">
    <p class=\"mb-0\"><strong>An error occured when we tried to add a swimmer</strong></p>
    <p>You may have got the Swim England Number or Access Key wrong</p>
    <p class=\"mb-0\">Please try again</p>
    </div>";
    header("Location: " . autoUrl("my-account/addswimmer"));
  }
}
else {
  // Error, fields not filled out
  $_SESSION['ErrorState'] = "
  <div class=\"alert alert-danger\">
  <p class=\"mb-0\"><strong>An error occured when we tried to add a swimmer</strong></p>
  <p>You may have got the Swim England Number or Access Key wrong</p>
  <p class=\"mb-0\">Please try again</p>
  </div>";
  header("Location: " . autoUrl("my-account/addswimmer"));
}
?>
