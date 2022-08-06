<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$getInfo = $db->prepare("SELECT members.MemberID, MForename fn, MSurname sn, members.UserID, trainingLogs.Title, trainingLogs.Content, trainingLogs.ContentType, trainingLogs.DateTime FROM trainingLogs INNER JOIN members ON trainingLogs.Member = members.MemberID WHERE trainingLogs.ID = ? AND members.Tenant = ?");
$getInfo->execute([
  $id,
  $tenant->getId()
]);
$info = $getInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

if (isset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AccessLevel']) && $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['AccessLevel'] == 'Parent' && $info['UserID'] != $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID']) {
  halt(404);
}

if (isset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['LogBooks-MemberLoggedIn']) && bool($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['LogBooks-MemberLoggedIn']) && $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['LogBooks-Member'] != $info['MemberID']) {
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

  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['EditLogErrorMessage'] = $errorMessage;

  http_response_code(303);
  header("location: " . autoUrl("log-books/logs/" . $id . "/edit"));

} else {
  // All good, insert to db

  try {

    $update = $db->prepare("UPDATE `trainingLogs` SET `DateTime` = ?, `Title` = ?, `Content` = ?, `ContentType` = ? WHERE ID = ?");
    $time = null;
    try {
      // Try datetime from seperate date and time inputs
      $dateTimeObject = DateTime::createFromFormat ("Y-m-d H:i", $_POST['date'] . ' ' . $_POST['time'], new DateTimeZone('Europe/London'));
      $dateTimeObject->setTimezone(new DateTimeZone('UTC'));
      $time = $dateTimeObject->format("Y-m-d H:i:s");
    } catch (Exception | Error $e) {
      $time = $info['DateTime'];
    }

    $update->execute([
      $time,
      $_POST['title'],
      trim($_POST['entry']),
      $_POST['content-type'],
      $id
    ]);

    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['EditLogSuccessMessage'] = true;

    http_response_code(303);
    // Temp redirect until log pages are added
    header("location: " . autoUrl("log-books/logs/" . $id . "/edit"));

  } catch (Exception $e) {
    $errorMessage = '<p class="mb-0"><strong>There was a problem saving your log entry changes to our database.</strong></p><ul class="mb-0">';
    $errorMessage .= "<li>" . $e->getMessage() . "</li>";
    $errorMessage .= "</ul>";

    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['EditLogErrorMessage'] = $errorMessage;

    http_response_code(303);
    header("location: " . autoUrl("log-books/logs/" . $id . "/edit"));
  }

}