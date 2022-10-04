<?php

$db = app()->db;
$tenant = app()->tenant;

$db->beginTransaction();

$responseCode = 200;
$responseData = [
  'status' => $responseCode,
  'message' => ''
];

try {

  if (empty($_POST['user']) || empty($_POST['password'])) {
    throw new Exception('Required details were not provided.');
  }

  // Get info for user we're deleting
  $getDeleteUser = $db->prepare("SELECT `Password`, Forename, Surname, EmailAddress FROM users WHERE UserID = ? AND Tenant = ?");
  $getDeleteUser->execute([
    $_POST['user'],
    $tenant->getId()
  ]);
  $deleteUser = $getDeleteUser->fetch(PDO::FETCH_ASSOC);

  if ($deleteUser == null) {
    throw new Exception('No such user exists, so we cannot delete the user.');
  }

  // Get user info to verify password
  $getCurrentUser = $db->prepare("SELECT `Password`, Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
  $getCurrentUser->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
  ]);
  $currentUser = $getCurrentUser->fetch(PDO::FETCH_ASSOC);

  if ($currentUser == null) {
    throw new Exception('Current user does not exist. This means an unknown error occurred.');
  }

  if (!password_verify($_POST['password'], $currentUser['Password'])) {
    throw new Exception('The password provided was incorrect.');
  }

  // Delete things

  // Delete emergency contacts
  $delete = $db->prepare("DELETE FROM emergencyContacts WHERE UserID = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Delete linked accounts
  $delete = $db->prepare("DELETE FROM linkedAccounts WHERE User = :userid OR LinkedUser = :userid");
  $delete->execute([
    'userid' => $_POST['user']
  ]);

  // Delete from list senders
  $delete = $db->prepare("DELETE FROM listSenders WHERE User = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Unset user id of any existing members
  $unset = $db->prepare("UPDATE members SET UserID = NULL WHERE UserID = ?");
  $unset->execute([
    $_POST['user']
  ]);

  // Delete notify history
  $delete = $db->prepare("DELETE FROM notify WHERE UserID = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Delete notify additional emails
  $delete = $db->prepare("DELETE FROM notifyAdditionalEmails WHERE UserID = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Delete notify options
  $delete = $db->prepare("DELETE FROM notifyOptions WHERE UserID = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Delete any password tokens
  $delete = $db->prepare("DELETE FROM passwordTokens WHERE UserID = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Revoke all permissions
  $delete = $db->prepare("DELETE FROM `permissions` WHERE User = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Delete squad rep assignments
  $delete = $db->prepare("DELETE FROM squadReps WHERE User = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Remove from targeted lists
  $delete = $db->prepare("DELETE FROM targetedListMembers WHERE ReferenceID = ? AND ReferenceType = ?");
  $delete->execute([
    $_POST['user'],
    'User'
  ]);

  // Delete team manager assignments
  $delete = $db->prepare("DELETE FROM teamManagers WHERE User = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Delete user logins
  $delete = $db->prepare("DELETE FROM userLogins WHERE UserID = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Clear user options
  $delete = $db->prepare("DELETE FROM userOptions WHERE User = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Delete coach assignments
  $delete = $db->prepare("DELETE FROM coaches WHERE User = ?");
  $delete->execute([
    $_POST['user']
  ]);

  // Get active direct debit mandates and try to cancel them
  $getMandates = $db->prepare("SELECT Mandate, MandateID FROM paymentMandates WHERE UserID = ? AND InUse = 1");
  $getMandates->execute([
    $_POST['user']
  ]);

  // Set out of use statement
  $setOutOfUse = $db->prepare("UPDATE paymentMandates SET InUse = 0 WHERE MandateID = ?");

  // Loop over all active mandates
  while ($oldMandate = $getMandates->fetch(PDO::FETCH_ASSOC)) {
    try {
      // Cancel the mandate
      try {
        include BASE_PATH . 'controllers/payments/GoCardlessSetupClient.php';
        $client->mandates()->cancel($oldMandate['Mandate']);
      } catch (Exception $e) {
        // Ignore - no GC
      }

      // Only set OOU in DB if above does not throw exception
      $setOutOfUse->execute([
        $oldMandate['MandateID']
      ]);
    } catch (Exception $e) {
      // Returns cancellation_failed error on failure
      // Oops can't cancel
    }
  }

  // Delete old preferred mandates if existing
  $deletePref = $db->prepare("DELETE FROM paymentPreferredMandate WHERE UserID = ?");
  $deletePref->execute([
    $_POST['user']
  ]);

  // Clear user contact details and set inactive
  $update = $db->prepare("UPDATE users SET EmailAddress = ?, EmailComms = ?, Mobile = ?, MobileComms = ?, Active = ? WHERE UserID = ?");
  $update->execute([
    hash('sha256', $_POST['user']),
    0,
    hash('sha256', $_POST['user']),
    0,
    0,
    $_POST['user']
  ]);

  // Commit
  $db->commit();

  // Send emails
  // TODO

  $responseData['status'] = 200;
  $responseData['message'] = $deleteUser['Forename'] . '\'s account has been deleted successfully.';
} catch (PDOException $e) {
  $responseData['status'] = 500;
  $responseData['message'] = 'A database error occurred. All changes have been rolled back. If direct debit mandates were cancelled, this cannot be rolled back and you may need to ask the user to setup their direct debit again.';
  $db->rollBack();
} catch (Exception $e) {
  $responseData['status'] = 500;
  $responseData['message'] = $e->getMessage();
  $db->rollBack();
} finally {
  http_response_code(200);
  echo json_encode($responseData);
}
