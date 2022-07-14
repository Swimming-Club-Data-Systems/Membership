<?php

if (!\SCDS\CSRF::verify()) halt(403);

// Find user
$db = app()->db;
$tenant = app()->tenant;

$db->beginTransaction();

use Respect\Validation\Validator as v;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
use Ramsey\Uuid\Nonstandard\Uuid;
use Ramsey\Uuid\Uuid as UuidUuid;

$user = null;
$status = true;

$email = trim(mb_strtolower($_POST['user-email']));

if (!isset($_POST['user'])) {
  $getUserInfo = $db->prepare("SELECT UserID FROM users WHERE EmailAddress = ? AND Tenant = ?");

  $getUserInfo->execute([
    $email,
    $tenant->getId()
  ]);

  if (!v::email()->validate($email)) {
    $status = false;
  }

  $user = $getUserInfo->fetchColumn();
} else {

  $getUser = $db->prepare("SELECT UserID FROM users WHERE UserID = ? AND Tenant  = ?");
  $getUser->execute([
    $_POST['user'],
    $tenant->getId(),
  ]);
  $user = $getUser->fetchColumn();

  if (!$user) halt(403);
}

if (!$user) {
  // Check details and create a new user

  $forename = trim($_POST['first']);
  $surname = trim($_POST['last']);

  if (mb_strlen($forename) < 1 || mb_strlen($surname) < 1) {
    $status = false;
  }

  $password = hash('sha256', random_int(PHP_INT_MIN, PHP_INT_MAX));

  $mobile = null;
  try {
    $number = PhoneNumber::parse($_POST['phone'], 'GB');
    $mobile = $number->format(PhoneNumberFormat::E164);
  } catch (PhoneNumberParseException $e) {
    // 'The string supplied is too short to be a phone number.'
    $status = false;
  }

  $insert = $db->prepare("INSERT INTO users (EmailAddress, `Password`, Forename, Surname, Mobile, EmailComms, MobileComms, RR, Tenant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $addAccessLevel = $db->prepare("INSERT INTO `permissions` (`Permission`, `User`) VALUES (?, ?)");

  // reportError($status);

  if ($status) {

    $insert->execute([
      $email,
      password_hash($password, PASSWORD_BCRYPT),
      $forename,
      $surname,
      $mobile,
      0,
      0,
      0,
      $tenant->getId()
    ]);

    $user = $db->lastInsertId();

    // reportError($user);

    $addAccessLevel->execute([
      'Parent',
      $user
    ]);
  }
} else {
  // No check required
  // reportError($user);
  // reportError('EXISTS');
}

$hasMembers = false;

if ($user) {

  // Ensure members are selected
  $swimmers = $db->prepare("SELECT MemberID `id` FROM members WHERE Active AND Tenant = ? AND members.UserID IS NULL");
  $swimmers->execute([
    $tenant->getId()
  ]);

  $selectedSwimmers = [];
  $setParent = $db->prepare("UPDATE members SET UserID = ? WHERE MemberID = ?");

  while ($swimmer = $swimmers->fetch(PDO::FETCH_ASSOC)) {
    if ($_POST['member-' . $swimmer['id']]) {
      $selectedSwimmers[] = $swimmer['id'];
      $setParent->execute([
        $user,
        $swimmer['id']
      ]);

      $hasMembers = true;
    }
  }

  // Fetch current members
  $getMembers = $db->prepare("SELECT MemberID id FROM members WHERE UserID = ? AND Active ORDER BY MForename ASC, MSurname ASC");
  $getMembers->execute([
    $user,
  ]);
  while ($swimmer = $getMembers->fetch(PDO::FETCH_ASSOC)) {
    if ($_POST['exisiting-member-' . $swimmer['id']]) {
      $selectedSwimmers[] = $swimmer['id'];
      // Already set parent here!

      $hasMembers = true;
    }
  }
}

http_response_code(302);
if (!$hasMembers || !$status) {
  $db->rollBack();

  // Redirect back to page
  if (isset($_POST['user'])) {
    header("location: " . autoUrl('onboarding/new?user=' . $_POST['user']));
  } else {
    header("location: " . autoUrl('onboarding/new'));
  }
} else {

  // Create onboarding session

  $id = Uuid::uuid4();
  $now = new DateTime('now', new DateTimeZone('UTC'));
  $today = new DateTime('now', new DateTimeZone('Europe/London'));
  $welcomeText = null;
  $stages = json_decode(json_encode(\SCDS\Onboarding\Session::getDefaultStages()));
  $metadata = [];

  if ($user) {
    // Don't go through set password step (account_details)
    $stages->account_details->required = false;
  }

  // Add to db
  $add = $db->prepare("INSERT INTO onboardingSessions (`id`,  `user`, `created`, `creator`, `start`, `charge_outstanding`, `charge_pro_rata`, `welcome_text`, `token`, `token_on`, `status`, `due_date`, `completed_at`, `stages`, `metadata`, `batch`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $add->execute([
    $id,
    $user,
    $now->format('Y-m-d H:i:s'),
    app()->user->getId(),
    $today->format('Y-m-d'),
    (int) false,
    (int) false,
    $welcomeText,
    hash('sha256', random_int(PHP_INT_MIN, PHP_INT_MAX)),
    (int) false,
    'not_ready',
    null,
    null,
    json_encode($stages),
    json_encode($metadata),
    null,
  ]);

  // Add members
  $add = $db->prepare("INSERT INTO onboardingMembers (`id`, `session`, `member`, `stages`) VALUES (?, ?, ?, ?)");

  foreach ($selectedSwimmers as $member) {

    $memberObj = new \Member($member);
    $memberStages = json_decode(json_encode(\SCDS\Onboarding\Member::getDefaultStages()));

    // Don't require photo consent for adults
    if ($memberObj->getAge() >= 18) {
      $memberStages->photography_consent->required = false;
    }

    $add->execute([
      Uuid::uuid4(),
      $id,
      $member,
      json_encode($memberStages),
    ]);
  }

  header("location: " . autoUrl("onboarding/sessions/a/$id"));

  $db->commit();
}
