<?php

$db = app()->db;
$tenant = app()->tenant;

$db->beginTransaction();

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['AssRegUser'];

try {
  $swimmers = $db->prepare("SELECT MemberID `id` FROM members WHERE Active AND Tenant = ? AND members.UserID IS NULL");
  $swimmers->execute([
    $tenant->getId()
  ]);

  $setParent = $db->prepare("UPDATE members SET UserID = ?, RR = ? WHERE MemberID = ?");

  $setUserRequiresRenewal = $db->prepare("UPDATE users SET RR = ? WHERE UserID = ?");

  $user = $db->prepare("SELECT Forename `first`, Surname `last`, EmailAddress `email` FROM users WHERE UserID = ?");
  $user->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AssRegUser']]);
  $user = $user->fetch(PDO::FETCH_ASSOC);

  if ($user == null) {
    halt(404);
  }

  $selectedSwimmers = [];

  $success = false;
  $requireRegistration = false;

  while ($swimmer = $swimmers->fetch(PDO::FETCH_ASSOC)) {
    if ($_POST['member-' . $swimmer['id']]) {
      $rr = true;
      if (isset($_POST['member-rr-' . $swimmer['id']])) {
        $rr = ($_POST['member-rr-' . $swimmer['id']] == 'yes');
      }
      if ($rr) {
        $requireRegistration = true;
      }
      $selectedSwimmers[] = $swimmer['id'];
      $setParent->execute([
        $_SESSION['TENANT-' . app()->tenant->getId()]['AssRegUser'],
        (int) ($tenant->getBooleanKey('REQUIRE_FULL_REGISTRATION') && $rr),
        $swimmer['id']
      ]);
      $success = true;
    }
  }

  $setUserRequiresRenewal->execute([
    (int) ($tenant->getBooleanKey('REQUIRE_FULL_REGISTRATION') && $requireRegistration),
    $_SESSION['TENANT-' . app()->tenant->getId()]['AssRegUser']
  ]);

  $db->commit();
} catch (Exception $e) {
  $db->rollBack();
  $success = false;
  reportError($e);
}

if ($success) {
  // Go to next page
  $subject = "Complete your registration at " . app()->tenant->getKey('CLUB_NAME');
  $message = "<p>Hello " . htmlspecialchars($user['first']) . ", </p>";
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AssRegExisting']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AssRegExisting']) {
    $message .= "<p>We've added a new member to your " . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . " account. We now need you to <a href=\"" . autoUrl("") . "\">sign in and provide some information</a>.</p>";
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['AssRegExisting']);
  } else {
    $message .= "<p>We've created an account for you in our membership system. We use the system to keep track of all our members, information, gala entries, payments and more.</p>";
    $message .= "<p>To continue, <a href=\"" . autoUrl("assisted-registration/" . $_SESSION['TENANT-' . app()->tenant->getId()]['AssRegUser'] . "/" . $_SESSION['TENANT-' . app()->tenant->getId()]['AssRegPass']) . "\">please follow this link</a></p>";
    if ($tenant->getBooleanKey('REQUIRE_FULL_REGISTRATION')) {
      $message .= "<p>As part of the registration process, we'll ask you to set a password, let us know your communication preferences and fill in important information about you and/or your members. At the end, we'll set up a direct debit so that payments to " . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . " are taken automatically.</p>";
      $message .= "<p>You'll also be given the opportunity to set up a direct debit.</p>";
    }
  }
  $message .= '<p>Please note that ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' may not provide all services included in the membership software.</p>';

  notifySend(null, $subject, $message, $user['first'] . ' ' . $user['last'], $user['email']);

  $_SESSION['TENANT-' . app()->tenant->getId()]['AssRegComplete'] = true;
  $_SESSION['TENANT-' . app()->tenant->getId()]['AssRegName'] = $user['first'] . ' ' . $user['last'];
  header("Location: " . autoUrl("assisted-registration/complete"));
} else {
  header("Location: " . autoUrl("assisted-registration/select-swimmers"));
}
