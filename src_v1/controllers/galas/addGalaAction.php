<?php

$db = app()->db;
$tenant = app()->tenant;
use Respect\Validation\Validator as v;

$status = true;
$statusInfo = "";

$galaName = $description = $length = $venue = $closingDate = $lastDate = $galaFee = $added = "";
$added = false;
$galaFeeConstant = $hyTek = 0;
$content = "";

if (!empty($_POST['galaname'])) {
  $galaName = trim($_POST['galaname']);
  if (strlen($galaName) == 0) {
    $status = false;
    $statusInfo .= "<li>No gala name was provided</li>";
  }
}

if (!empty($_POST['description'])) {
  $description = trim($_POST['description']);
}

if (!empty($_POST['length'])) {
  $length = trim($_POST['length']);
  if (strlen($length) == 0) {
    $status = false;
    $statusInfo .= "<li>There was a problem with the supplied course length</li>";
  }
}

if (!empty($_POST['venue'])) {
  $venue = trim($_POST['venue']);
  if (strlen($venue) == 0) {
    $status = false;
    $statusInfo .= "<li>You failed to supply a place name</li>";
  }
}

if (!empty($_POST['closingDate']) && !empty($_POST['closingTime']) && v::date()->validate($_POST['closingDate']) && v::time('H:i')->validate($_POST['closingTime'])) {
  $date = DateTime::createFromFormat('Y-m-d H:i', $_POST['closingDate'] . ' ' . $_POST['closingTime'], new DateTimeZone('Europe/London'));
  $closingDate = $date->format('Y-m-d H:i:s');
} else {
  $status = false;
  $statusInfo .= "<li>The closing date and time was malformed and not understood clearly by the system</li>";
}

if (!empty($_POST['lastDate']) && v::date()->validate($_POST['lastDate'])) {
  $date = strtotime($_POST['lastDate']);
  $lastDate = date("Y-m-d", $date);
} else {
  $status = false;
  $statusInfo .= "<li>The gala date was malformed and not understood clearly by the system</li>";
}

if (!empty($_POST['galaFee'])) {
  $galaFee = trim($_POST['galaFee']);
} else {
  $galaFee = 0.00;
}

if (isset($_POST['HyTek']) && bool($_POST['HyTek'])) {
  $hyTek = 1;
}

$coachDoesEntries = 0;
if (isset($_POST['coachDecides']) && bool($_POST['coachDecides'])) {
  $coachDoesEntries = 1;
}

$approvalNeeded = 0;
if (isset($_POST['approvalNeeded']) && bool($_POST['approvalNeeded'])) {
  $approvalNeeded = 1;
}

$processingFee = 0;
if (isset($_POST['per-entry-fee'])) {
  $processingFee = MoneyHelpers::decimalToInt($_POST['per-entry-fee']);
}

//$sql = "INSERT INTO `galas` (`GalaName`, `CourseLength`, `GalaVenue`, `ClosingDate`, `GalaDate`, `GalaFeeConstant`, `GalaFee`, `HyTek`) VALUES ('$galaName', '$length', '$venue', '$closingDate', '$lastDate', '$galaFeeConstant', '$galaFee', '$hyTek');";
//echo $sql;

if ($status) {
  $id = null;
  try {
    $query = $db->prepare("INSERT INTO `galas` (`GalaName`, `Description`, `CourseLength`, `GalaVenue`, `ClosingDate`, `GalaDate`, `GalaFeeConstant`, `GalaFee`, `HyTek`, `CoachEnters`, `RequiresApproval`, `ProcessingFee`, Tenant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $query->execute([
      $galaName,
      $description,
      $length,
      $venue,
      $closingDate,
      $lastDate,
      true,
      $galaFee,
      $hyTek,
      $coachDoesEntries,
      $approvalNeeded,
      $processingFee,
      $tenant->getId()
    ]);
    $added = true;
    $id = $db->lastInsertId();
  } catch (Exception $e) {
    reportError($e);
    $statusInfo .= "<li>Database error</li>";
  }
}

if ($id != null) {
  $galaData = new GalaPrices($db, $id);
  $galaData->setupDefault();
}

if ($added && $status) {
  $pagetitle = $title = "Gala Added";
  $content = "<p class=\"lead\">You have successfully added " . htmlspecialchars($galaName) . " to the database.</p>";
  $content .= "<p>It will be open for entries from parents until " . date('j F Y', strtotime($closingDate)) . " and stay visible to all users until " . date('j F Y', strtotime($lastDate)) . "</p>";
  if ($galaFeeConstant == 1) {
    $content .= "<p>The fee for each swim is &pound;" . number_format($galaFee,2,'.','') . "</p>";
  }
  $content .= "<p><a href=\"" . autoUrl("galas") . "\" class=\"btn
  btn-success\">Return to Galas</a> <a href=\"" . autoUrl("galas/addgala") . "\"
  class=\"btn btn-dark-l btn-outline-light-d\">Add another gala</a></p>";

  if ($id != null) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['GalaAddedSuccess'] = true;
    AuditLog::new('Galas-Added', 'Added ' . $galaName . ', #' . $id);
    if (bool($coachDoesEntries)) {
      header("Location: " . autoUrl("galas/" . $id . "/sessions"));
    } else {
      header("Location: " . autoUrl("galas/" . $id . "/pricing-and-events"));
    }
  } else {
    header("Location: " . autoUrl("galas"));
  }
}
else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = '
  <div class="alert alert-danger">
  <p class="mb-0">
  <strong>We were unable to add this gala</strong>
  </p>
  <p>The issue was</p>
  <ul class="mb-0">
  ' . $statusInfo . '
  </ul>
  </div>';
  header("location: " . autoUrl("galas/addgala"));
}
