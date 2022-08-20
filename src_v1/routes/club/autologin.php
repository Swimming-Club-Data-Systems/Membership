<?php

if (!isset($_GET['auth_code']) || mb_strlen($_GET['auth_code']) == 0) {
  header("location: " . autoUrl("login"));
  return;
} else {
  // Try to validate code

  $db = app()->db;
  $tenant = app()->tenant;

  $time = new DateTime('-15 minutes', new DateTimeZone('UTC'));

  $query = $db->prepare("SELECT UserID from v1_logins INNER JOIN users ON users.UserID = v1_logins.user_id WHERE users.Tenant = ? AND token = ? AND v1_logins.created_at >= ?");
  $query->execute([
    $tenant->getId(),
    $_GET['auth_code'],
    $time->format('Y-m-d H:i:d')
  ]);

  $result = $query->fetch(PDO::FETCH_ASSOC);

  if (!$result) {
    header("location: " . autoUrl("login"));
    return;
  } else {
    $user = new User($result['UserID']);

    $login = new \CLSASC\Membership\Login($db);
    $login->setUser($result['UserID']);
    $currentUser = app()->user;
    $currentUser = $login->login();

    $event = 'UserLogin-V2-LaravelRedirection';
    AuditLog::new($event, 'Signed in from ' . getUserIp(), $currentUser->getId());

    // Now delete the token to prevent replay
    $delete = $db->prepare("DELETE FROM `v1_logins` WHERE `token` = ?");
    $delete->execute([
      $_GET['auth_code']
    ]);

    $url = autoUrl("");
    if (isset($_SESSION['login_redirect_target'])) {
      $url = $_SESSION['login_redirect_target'];
    }

    header("location: " . $url);
  }

}