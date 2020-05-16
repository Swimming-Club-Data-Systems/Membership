<?php

$db = app()->db;

use Respect\Validation\Validator as v;

if (isset($_SESSION['AssRegUser']) && $_SESSION['AssRegUser']) {
  $_SESSION['AssRegUser'] = null;
  unset($_SESSION['AssRegUser']);
}

$getUserInfo = $db->prepare("SELECT UserID FROM users WHERE EmailAddress = ?");

$email = trim(mb_strtolower($_POST['email-address']));
$getUserInfo->execute([$email]);
  
$status = true;
if (!v::email()->validate($email)) {
  $status = false;
}

$info = $getUserInfo->fetch(PDO::FETCH_ASSOC);

if ($status && $info) {
  $_SESSION['AssRegUser'] = $info['UserID'];
  $_SESSION['AssRegExisting'] = true;

  // Check has parent permissions
  $count = $db->prepare("SELECT COUNT(*) FROM `permissions` WHERE `User` = ? AND `Permission` = ?");
  $count->execute([
    $info['UserID'],
    'Parent'
  ]);
  if ($count->fetchColumn() == 0) {
    $addAccessLevel = $db->prepare("INSERT INTO `permissions` (`Permission`, `User`) VALUES (?, ?)");
    $addAccessLevel->execute([
      'Parent',
      $info['UserID']
    ]);
  }

  header("Location: " . autoUrl("assisted-registration/select-swimmers"));
} else if ($status && $info == null) {
  // USER DOES NOT EXIST
  $_SESSION['AssRegUserEmail'] = $email;
  header("Location: " . autoUrl("assisted-registration/start"));
} else if (!$status) {
  // INVALID EMAIL ADDRESS
  $_SESSION['AssRegEmailError'] = 'INV-EMAIL';
  header("Location: " . autoUrl("assisted-registration#get-started"));
} else {
  // NOT A PARENT ACCOUNT
  $_SESSION['AssRegEmailError'] = 'NOT-PARENT';
  header("Location: " . autoUrl("assisted-registration#get-started"));
}