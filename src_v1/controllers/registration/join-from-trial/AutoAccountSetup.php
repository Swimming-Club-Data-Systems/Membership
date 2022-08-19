<?php

/*
 * Set up the user's account for them and then automatically transition
 * to a logged in state
 */

// First check if an account for this email already exists

$db = app()->db;

try {

  $checkExists = $db->prepare("SELECT COUNT(*) FROM users WHERE EmailAddress = ?");
  $checkExists->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['email-addr']]);

  $userID = null;

  if ($checkExists->fetchColumn() == 0) {
    // Create a new account
    try {
      $addUser = $db->prepare("INSERT INTO users (Password, AccessLevel, EmailAddress, EmailComms, Forename, Surname, Mobile, MobileComms, RR) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $addUser->execute([
        $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['password-hash'],
        'Parent',
        $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['email-addr'],
        $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['allow-email'],
        $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['forename'],
        $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['surname'],
        $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['mobile'],
        $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['allow-sms'],
        false
      ]);

      $userID = $db->lastInsertId();
    } catch (Exception $e) {
      halt(500);
    }
  } else {
    $getUser = $db->prepare("SELECT UserID FROM users WHERE EmailAddress = ?");
    $getUser->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['email-addr']]);
    $userID = $getUser->fetchColumn();
  }

  // Get swimmers from trial request
  $getSwimmers = $db->prepare("SELECT First, Last, DoB, SquadRecommendation, Sex FROM joinSwimmers WHERE Parent = ? AND SquadRecommendation IS NOT NULL");
  $getSwimmers->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash']]);

  $swimmers = $getSwimmers->fetchAll(PDO::FETCH_ASSOC);

  // Add the swimmers in suspended state
  $insertSwimmer = $db->prepare("INSERT INTO members (SquadID, UserID, Status, RR, MForename, MSurname, members.ASANumber, ASACategory, DateOfBirth, Gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  // For each swimmer, insert to database in suspended state, connected to this user.
  foreach ($swimmers as $swimmer) {
    $asa = null;
    if ($swimmer['ASA'] == "" || $swimmer['ASA'] == null) {
      $asa = $swimmer['ASA'];
    }

    $insertSwimmer->execute([
      $swimmer['SquadRecommendation'],
      $userID,
      false,
      true,
      $swimmer['First'],
      $swimmer['Last'],
      $asa,
      $asa_cat,
      $swimmer['DoB'],
      $swimmer['Sex']
    ]);
  }
} catch (Exception $e) {
  halt(500);
}

// Go to the medical form for swimmers who are RR
$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] = 'MedicalForm';
header("Location: " . autoUrl("register/ac/medical-form"));
