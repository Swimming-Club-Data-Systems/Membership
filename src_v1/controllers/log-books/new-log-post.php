<?php

$db = app()->db;
$tenant = app()->tenant;

$getMember = $db->prepare("SELECT MForename fn, MSurname sn, members.UserID FROM members WHERE members.MemberID = ? AND members.Tenant = ?");
$getMember->execute([
  $member,
  $tenant->getId()
]);
$memberInfo = $getMember->fetch(PDO::FETCH_ASSOC);

if ($memberInfo == null) {
  halt(404);
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $memberInfo['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
  halt(404);
}

// Authenticated by above code

// Handle log entry

// Check meets requirements

$errors = [];
$contentTypes = [
  'text/plain',
  'text/plain-font-monospacespace',
  'text/markdown'
];

if (!(isset($_POST['title']) && mb_strlen($_POST['title']) > 0)) {
  $errors[] = "The log entry does not have a title";
}

if (!(isset($_POST['entry']) && mb_strlen($_POST['entry']) > 0)) {
  $errors[] = "The log entry has no content";
}

if (!(isset($_POST['content-type']) && in_array($_POST['content-type'], $contentTypes))) {
  $errors[] = "The supplied content type was invalid";
}

if (sizeof($errors) > 0) {
  // There are some errors so go back to user to correct

  $errorMessage = '<p class="mb-0"><strong>There was a problem with the data you supplied.</strong></p><ul class="mb-0">';
  foreach ($errors as $error) {
    $errorMessage .= "<li>" . $error . "</li>";
  }
  $errorMessage .= "</ul>";

  $_SESSION['TENANT-' . app()->tenant->getId()]['AddLogErrorMessage'] = $errorMessage;
  $_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent'] = $_POST;

  http_response_code(303);
  header("location: " . autoUrl("log-books/members/" . $id . "/new"));

} else {
  // All good, insert to db

  try {

    $insert = $db->prepare("INSERT INTO `trainingLogs` (`Member`, `DateTime`, `Title`, `Content`, `ContentType`) VALUES (?, ?, ?, ?, ?)");
    $time = null;
    try {
      // Try datetime from seperate date and time inputs
      $dateTimeObject = DateTime::createFromFormat ("Y-m-d H:i", $_POST['date'] . ' ' . $_POST['time'], new DateTimeZone('Europe/London'));
      $dateTimeObject->setTimezone(new DateTimeZone('UTC'));
      $time = $dateTimeObject->format("Y-m-d H:i:s");
    } catch (Exception | Error $e) {
      $time = (new DateTime('now', new DateTimeZone('UTC')))->format("Y-m-d H:i:s");
    }

    $insert->execute([
      $member,
      $time,
      $_POST['title'],
      trim($_POST['entry']),
      $_POST['content-type']
    ]);

    $logId = $db->lastInsertId();

    $_SESSION['TENANT-' . app()->tenant->getId()]['AddLogSuccessMessage'] = $logId;

    http_response_code(302);
    // Temp redirect until log pages are added
    header("location: " . autoUrl("log-books/members/" . $member));
    // header("location: " . autoUrl("log-books/logs/" . $logId));

  } catch (Exception $e) {
    $errorMessage = '<p class="mb-0"><strong>There was a problem adding your log entry to our database.</strong></p><ul class="mb-0">';
    $errorMessage .= "<li>" . $e->getMessage() . "</li>";
    $errorMessage .= "</ul>";

    $_SESSION['TENANT-' . app()->tenant->getId()]['AddLogErrorMessage'] = $errorMessage;
    $_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent'] = $_POST;

    http_response_code(303);
    header("location: " . autoUrl("log-books/members/" . $id . "/new"));
  }

}