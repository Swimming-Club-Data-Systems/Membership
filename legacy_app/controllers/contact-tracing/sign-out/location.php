<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

// $dataInit = autoUrl('apis', false);
$dataInit = 'https://production-apis.tenant-services.membership.myswimmingclub.uk';
if (bool(getenv("IS_DEV"))) {
  $dataInit = 'https://apis.tenant-services.membership.myswimmingclub.uk';
}

$getLocation = $db->prepare("SELECT `ID`, `Name`, `Address` FROM covidLocations WHERE `ID` = ? AND `Tenant` = ?");
$getLocation->execute([
  $id,
  $tenant->getId()
]);
$location = $getLocation->fetch(PDO::FETCH_ASSOC);

if (!$location) {
  halt(404);
}

if (!app()->user) {
  halt(404);
}

// Check if authenticated
$getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
$getRepCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
]);
$showSignOut = $getRepCount->fetchColumn() > 0;

$user = app()->user;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSignOut = true;
}
if (!$showSignOut) {
  halt(404);
}

$earliest = new DateTime('-3 weeks', new DateTimeZone('Europe/London'));
$latest = new DateTime('now', new DateTimeZone('Europe/London'));

$from = new DateTime('-3 hours', new DateTimeZone('Europe/London'));
$to = new DateTime('now', new DateTimeZone('Europe/London'));

if (isset($_GET['from-date']) && isset($_GET['from-time']) && isset($_GET['to-date']) && isset($_GET['to-time'])) {
  try {
    $from = DateTime::createFromFormat("Y-m-d H:i", $_GET['from-date'] . ' ' . $_GET['from-time'], new DateTimeZone('Europe/London'));
    $to = DateTime::createFromFormat("Y-m-d H:i", $_GET['to-date'] . ' ' . $_GET['to-time'], new DateTimeZone('Europe/London'));
  } catch (Exception $e) {
  }
}

$fromUTC = clone $from;
$toUTC = clone $to;

$fromUTC->setTimezone(new DateTimeZone('UTC'));
$toUTC->setTimezone(new DateTimeZone('UTC'));

// Get Squad Members
$getVisitors = $db->prepare("SELECT `ID`, `GuestName`, `GuestPhone`, `Person`, `Type`, `Time`, `SignedOut` FROM `covidVisitors` WHERE `Location` = :loc AND `Time` >= :startTime AND `Time` <= :endTime ORDER BY GuestName ASC, `Time` ASC;");
$getVisitors->execute([
  'loc' => $id,
  'startTime' => $fromUTC->format('Y-m-d H:i:s'),
  'endTime' => $toUTC->format('Y-m-d H:i:s'),
]);

$pagetitle = 'Sign Out of ' . htmlspecialchars($location['Name']) . ' - Contact Tracing';

$locationAddress = json_decode($location['Address']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations')) ?>">Locations</a></li>
        <li class="breadcrumb-item active" aria-current="page">Sign Out</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Sign Out of <?= htmlspecialchars($location['Name']) ?>
        </h1>
        <p class="lead mb-0">
          <?= htmlspecialchars($locationAddress->streetAndNumber) ?>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>An error occurred</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']['message']) ?>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']);
      } ?>

      <form action="<?= htmlspecialchars(autoUrl('contact-tracing/sign-out/' . $id)) ?>" method="get">
        <div class="mb-3">
          <p class="mb-2">
            From
          </p>
          <div class="row">
            <div class="col">
              <input type="date" class="form-control" name="from-date" id="from-date" min="<?= htmlspecialchars($earliest->format('Y-m-d')) ?>" max="<?= htmlspecialchars($latest->format('Y-m-d')) ?>" required placeholder="<?= htmlspecialchars($from->format('Y-m-d')) ?>" value="<?= htmlspecialchars($from->format('Y-m-d')) ?>">
            </div>
            <div class="col">
              <input type="time" class="form-control" name="from-time" id="from-time" required placeholder="<?= htmlspecialchars($from->format('H:i')) ?>" value="<?= htmlspecialchars($from->format('H:i')) ?>">
            </div>
          </div>
        </div>

        <div class="mb-3">
          <p class="mb-2">
            To
          </p>
          <div class="row">
            <div class="col">
              <input type="date" class="form-control" name="to-date" id="to-date" min="<?= htmlspecialchars($earliest->format('Y-m-d')) ?>" max="<?= htmlspecialchars($latest->format('Y-m-d')) ?>" required placeholder="<?= htmlspecialchars($to->format('Y-m-d')) ?>" value="<?= htmlspecialchars($to->format('Y-m-d')) ?>">
            </div>
            <div class="col">
              <input type="time" class="form-control" name="to-time" id="to-time" required placeholder="<?= htmlspecialchars($to->format('H:i')) ?>" value="<?= htmlspecialchars($to->format('H:i')) ?>">
            </div>
          </div>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Update selection
          </button></p>
      </form>


      <?php if ($visitor = $getVisitors->fetch(PDO::FETCH_ASSOC)) { ?>

        <form>

          <input type="hidden" name="from-time" value="<?= htmlspecialchars($fromUTC->format('c')) ?>">
          <input type="hidden" name="to-time" value="<?= htmlspecialchars($toUTC->format('c')) ?>">

          <p>
            Tick people who have left.
          </p>

          <?= \SCDS\CSRF::write() ?>

          <ul class="list-group mb-3" id="checkboxes">
            <?php do {
              $date = new DateTime($visitor['Time'], new DateTimeZone('UTC'));
              $date->setTimezone(new DateTimeZone('Europe/London'));
            ?>
            <li class="list-group-item">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="<?= htmlspecialchars('visitor-' . $visitor['ID']) ?>" name="<?= htmlspecialchars('visitor-' . $visitor['ID']) ?>" value="1" data-id="<?= htmlspecialchars($visitor['ID']) ?>" <?php if (bool($visitor['SignedOut'])) { ?>checked<?php } ?> >
                <label class="form-check-label d-block" for="<?= htmlspecialchars('visitor-' . $visitor['ID']) ?>"><?= htmlspecialchars($visitor['GuestName']) ?> <small>Arrived <?= htmlspecialchars($date->format('H:i, j F')) ?></small>
              </div>
            </li>
            <?php } while ($visitor = $getVisitors->fetch(PDO::FETCH_ASSOC)); ?>
            </ul>
        </form>

      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>There are no people to Sign Out of <?= htmlspecialchars($location['Name']) ?></strong>
          </p>
          <p class="mb-0">
            Try changing your time frame
          </p>
        </div>
      <?php } ?>

    </div>
  </div>

  <div id="socket-info" data-init="<?= htmlspecialchars($dataInit) ?>" data-room="<?= htmlspecialchars($id) ?>" data-ajax-url="<?= htmlspecialchars(autoUrl('contact-tracing/sign-out/ajax')) ?>"></div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
if (bool(getenv("IS_DEV"))) {
  $footer->addExternalJs('https://apis.tenant-services.membership.myswimmingclub.uk/socket.io/socket.io.js');
} else {
  $footer->addExternalJs('https://production-apis.tenant-services.membership.myswimmingclub.uk/socket.io/socket.io.js');
}
// $footer->addExternalJs(autoUrl('apis/socket.io/socket.io.js', false));
$footer->addJs('public/js/contact-tracing/sign-out.js');
$footer->render();
