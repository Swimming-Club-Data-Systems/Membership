<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!SCDS\CSRF::verify() || !SCDS\FormIdempotency::verify()) {
    throw new Exception('There was a Cross Site Request Forgery or Form Idempotency issue. Please try again.');
  }

  $db->beginTransaction();

  $type = 'Payment';
  $typeString = 'charge';
  if ($_POST['type'] == 'Credit') {
    $type = 'Refund';
    $typeString = 'credit (refund)';
  } else if ($_POST['type'] != 'Payment') {
    throw new Exception('Invalid payment item type (must be Payment or Credit).');
  }

  $user = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ? AND Tenant = ?");
  $user->execute([
    $_POST['user-id'],
    $tenant->getId()
  ]);
  $user = $user->fetch(PDO::FETCH_ASSOC);

  if ($user == null) {
    throw new Exception('User not found or invalid.');
  }

  $description = mb_ucfirst($_POST['description']);
  $descriptionLength = mb_strlen($description);
  if (!($descriptionLength > 0)) {
    throw new Exception('The description is too short.');
  }

  if (!($descriptionLength < 500)) {
    throw new Exception('The description is too long.');
  }

  // Try to get category
  $category = null;
  if (isset($_POST['payment-category']) && $_POST['payment-category'] != 'none') {
    $getCategory = $db->prepare("SELECT ID FROM paymentCategories WHERE UniqueID = ? AND Tenant = ?");
    $getCategory->execute([
      $_POST['payment-category'],
      $tenant->getId(),
    ]);
    $category = $getCategory->fetchColumn();
  }

  $addToPaymentsPending = $db->prepare("INSERT INTO paymentsPending (`Date`, `Status`, UserID, `Name`, Amount, Currency, `Type`, `Category`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

  $date = new DateTime('now', new DateTimeZone('Europe/London'));

  $amountDec = \Brick\Math\BigDecimal::of((string) $_POST['amount']);
  $amount = $amountDec->withPointMovedRight(2)->toInt();
  $amountString = (string) $amountDec->toScale(2);

  if ($amount < 0) {
    throw new Exception('The amount entered is either less than £0.');
  }

  if ($amount > 100000) {
    throw new Exception('The amount entered is more than £1000.');
  }

  $addToPaymentsPending->execute([
    $date->format('Y-m-d'),
    'Pending',
    $_POST['user-id'],
    $description,
    $amount,
    'GBP',
    $type,
    $category,
  ]);

  $id = $db->lastInsertId();

  if ($amount > 0) {
    // Send an email to the user
    $subject = 'Payments: New ';
    if ($type == 'Payment') {
      $subject .= 'charge on account';
    } else {
      $subject .= 'credit/refund on account';
    }
    $message .= '<p>Hi ' . htmlspecialchars($user['Forename']) . ', </p>';
    $message .= '<p>We\'ve manually added a ' . $typeString . ' of <strong>&pound;' . $amountString . '</strong> to your next ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' direct debit payment for <strong>' . htmlspecialchars($description) .  '</strong>.</p><p>You will be able to see this charge in your pending charges and from the first day of next month, on your bill statement. You\'ll be charged for this as part of your next direct debit payment to ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . '.</p>';

    $message .= '<p>Kind Regards, <br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Payments Team</p>';

    $notify = $db->prepare("INSERT INTO notify (UserID, `Status`, `Subject`, `Message`, EmailType) VALUES (?, ?, ?, ?, ?)");

    notifySend(null, $subject, $message, $user['Forename'] . " " . $user['Surname'], $user['EmailAddress']);

    $notify->execute([
      $_POST['user-id'],
      'Sent',
      $subject,
      $message,
      'Payments'
    ]);
  }

  $db->commit();

  AuditLog::new('Payments-Invoices-Added', 'Created payment item #' . $id);

  $_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentSuccessMessage'] = 'We\'ve added the ' . $typeString . ' to ' . $user['Forename'] . '\'s account to pay in their next bill.';
} catch (PDOException $e) {
  reportError($e);
  $db->rollBack();

  $_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentErrorMessage'] = 'A database error occurred. Please try again. If the error occurs again, please try again later.';
} catch (Exception $e) {
  reportError($e);
  if ($db->inTransaction()) {
    try {
      $db->rollBack();
    } catch (Exception $e) {
      // Ignore
    }
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['NewPaymentErrorMessage'] = $e->getMessage();
}

header('Location: ' . autoUrl('payments/invoice-payments/new'));
