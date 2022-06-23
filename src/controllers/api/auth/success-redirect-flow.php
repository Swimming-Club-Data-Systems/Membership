<?php

// Try finding user

$db = app()->db;
$tenant = app()->tenant;

$output = [
  'success' => false,
  'message' => "An unknown error occurred",
];

try {

  if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['REACT_LOGIN_USER_CONFIRMED'])) {
    throw new Exception("Not ready, no user");
  }

  $login = new \CLSASC\Membership\Login($db);
  $login->setUser($_SESSION['TENANT-' . app()->tenant->getId()]['REACT_LOGIN_USER_CONFIRMED']);
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['REACT_LOGIN_REMEMBER_ME']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['REACT_LOGIN_REMEMBER_ME'])) {
    $login->stayLoggedIn();
  }
  $currentUser = app()->user;
  $currentUser = $login->login();

  $location = autoUrl("");

  if (isset($_GET['target'])) {
    $location = $_GET['target'];
  }

  $output = [
    'success' => true,
    'message' => "You are logged in",
  ];

  header("location: " . $location);
} catch (Exception $e) {
  echo json_encode($output);
}
