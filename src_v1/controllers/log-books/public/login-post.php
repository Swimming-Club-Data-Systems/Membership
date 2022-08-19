<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  // Inner try/catch for PDOExceptions
  try {

    if (!SCDS\CSRF::verify()) {
      throw new Exception('We could not verify the integrity of your login attempt. A malicious site may have just attempted to steal your login details.');
    }

    // Swim England numbers in system may not be unique
    // Check count is <= 1
    $getCount = $db->prepare("SELECT COUNT(*) FROM members WHERE ASANumber = ? AND Tenant = ?");
    $getCount->execute([
      $_POST['swim-england'],
      $tenant->getId()
    ]);
    if ($getCount->fetchColumn() > 1) {
      throw new Exception('Your Swim England number is not unique in the ' . app()->tenant->getKey('CLUB_NAME') . ' database. We cannot log you in with ambiguous details.');
    } 

    $getMember = $db->prepare("SELECT MemberID, ASANumber, PWHash, PWWrong FROM members WHERE ASANumber = ? AND Tenant = ?");
    $getMember->execute([
      trim($_POST['swim-england']),
      $tenant->getId()
    ]);

    $member = $getMember->fetch(PDO::FETCH_ASSOC);

    if ($member == null) {
      throw new Exception('Your Swim England number or password was invalid');
    }

    if ($member['PWWrong'] > 3) {
      throw new Exception('You\'ve entered the wrong password too many times so your account has been locked for your own security. Please ask your primary account holder to reset it.');
    }

    if (!password_verify($_POST['password'], $member['PWHash'])) {
      $incrementWrongPW = $db->prepare("UPDATE members SET PWWrong = PWWrong + 1 WHERE MemberID = ?");
      $incrementWrongPW->execute([
        $member['MemberID']
      ]);
      throw new Exception('Your Swim England number or password was invalid');
    }

    // User has been validated
    // Reset PW Wrong count
    $resetWrongPW = $db->prepare("UPDATE members SET PWWrong = 0 WHERE MemberID = ?");
    $resetWrongPW->execute([
      $member['MemberID']
    ]);

    $_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-MemberLoggedIn'] = true;
    $_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-Member'] = $member['MemberID'];

    if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['TARGET_URL'])) {
      unset($_SESSION['TENANT-' . app()->tenant->getId()]['TARGET_URL']);
    }

    http_response_code(303);
    header("location: " . autoUrl("log-books"));
  
  } catch (PDOException $e) {

    // Was a DB error - throw generic exception so info isn't shown
    throw new Exception('A database error occurred. Your club staff may need to check there are no pending database migrations.');

  }

} catch (Exception $e) {

  // Invalid attempt
  // Return and report error to user
  $_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-SE-ID'] = $_POST['swim-england'];
  $_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-MemberLoginError'] = $e->getMessage();

  http_response_code(303);
  header("location: " . autoUrl("log-books/login"));

}