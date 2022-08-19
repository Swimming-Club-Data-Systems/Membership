<?php

$db = app()->db;
$tenant = app()->tenant;

$getMember = $db->prepare("SELECT MForename fn, MSurname sn, ASANumber se, UserID `uid` FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-Member'],
  $tenant->getId()
]);
$member = $getMember->fetch(PDO::FETCH_ASSOC);

if ($member == null) {
  halt(404);
}

///////////////////////////////////////////////////////////////////////////////

use Respect\Validation\Validator as v;

try {
  try {
    if (!v::stringType()->length(7, null)->validate($_POST['password-1'])) {
      throw new Exception('Password does not meet the password length requirements. Passwords must be 8 characters or longer');
    }

    if ($_POST['password-1'] != $_POST['password-2']) {
      throw new Exception('Passwords do not match');
    }

    $hash = password_hash($_POST['password-1'], PASSWORD_BCRYPT);

    $setPw = $db->prepare("UPDATE members SET PWHash = ?, PWWrong = 0 WHERE MemberID = ?");
    $setPw->execute([
      $hash,
      $id
    ]);

    $_SESSION['TENANT-' . app()->tenant->getId()]['SetMemberPassSuccess'] = true;

    http_response_code(303);
    if (isset($_POST['return'])) {
      header("location: " . $_POST['return']);
    } else {
      header("location: " . autoUrl("log-books"));
    }
  } catch (PDOException $e) {
    // Was a DB error - throw generic exception so info isn't shown
    throw new Exception('A database error occurred. Your club staff may need to check there are no pending database migrations.');
  }

} catch (Exception $e) {

  $_SESSION['TENANT-' . app()->tenant->getId()]['SetMemberPassError'] = $e->getMessage();
  http_response_code(303);
  header("location: " . autoUrl("log-books/settings/password"));

}