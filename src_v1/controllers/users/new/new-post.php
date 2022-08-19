<?php

/**
 * Handle new user creation
 */

use Respect\Validation\Validator as v;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

try {
  if (!SCDS\CSRF::verify()) {
    throw new Exception('Cross Site Request Forgery Verification failed');
  }

  $forename = trim(ucwords($_POST['first-name']));
  $surname = trim(ucwords($_POST['last-name']));
  $password1 = trim($_POST['password-1']);
  $password2 = trim($_POST['password-2']);

  if ($password1 != $password2) {
    throw new Exception('Passwords do not match');
  }

  $hash = password_hash($password1, PASSWORD_ARGON2ID);

  $email = mb_strtolower(trim($_POST['email-address']));
  $mobile = null;
  try {
    $number = PhoneNumber::parse($_POST['phone'], 'GB');
    $mobile = $number->format(PhoneNumberFormat::E164);
  } catch (PhoneNumberParseException $e) {
    throw new Exception('Invalid phone number');
  }

  if (!v::stringType()->length(7, null)->validate($password1)) {
    throw new Exception('Password does not meet the minimum requirements');
  }
  
  if (!v::email()->validate($email)) {
    throw new Exception('Email address is invalid');
  }

  $db = app()->db;
  $tenant = app()->tenant;

  $getUserCount = $db->prepare("SELECT COUNT(*) FROM users WHERE EmailAddress = ? AND Tenant = ?");
  $getUserCount->execute([
    $email,
    $tenant->getId(),
  ]);

  if ($getUserCount->fetchColumn() > 0) {
    throw new Exception('Email address already in use');
  }

  $id = null;

  try {
    $db->beginTransaction();

    // Add the user to the DB
    $add = $db->prepare("INSERT INTO users (`EmailAddress`, `Password`, `Forename`, `Surname`, `Mobile`, `EmailComms`, `MobileComms`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $add->execute([
      $email,
      $hash,
      $forename,
      $surname,
      $mobile,
      0,
      0,
      $tenant->getId()
    ]);

    $id = $db->lastInsertId();

    // Add user permissions
    $addPermission = $db->prepare("INSERT INTO `permissions` (`User`, `Permission`) VALUES (?, ?)");
    $permissions = [
      'permissions-admin' => 'Admin',
      'permissions-coach' => 'Coach',
      'permissions-gala' => 'Galas',
    ];
    $hasOne = false;

    foreach ($permissions as $postedName => $permission) {
      if (isset($_POST[$postedName]) && bool($_POST[$postedName])) {
        $addPermission->execute([
          $id,
          $permission
        ]);
        $hasOne = true;
      }
    }

    // If no permissions, default to low-level parent permissions
    if (!$hasOne) {
      $addPermission->execute([
        $id,
        'Parent'
      ]);
    }

    $db->commit();
  } catch (PDOException $e) {
    $db->rollBack();
    throw new Exception('A database error occurred');
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationSuccess'] = true;
  header("location: " . autoUrl("users/$id"));
} catch (PDOException $e) {
  throw new Exception('A database error occurred');
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError'] = [
    'message' => $e->getMessage(),
    'fields' => $_POST
  ];
  header("location: " . autoUrl('users/add'));
}
