<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();
$user = Auth::User()->getLegacyUser();

include 'my-members/my-members.php';
include 'all-members/all-members.php';

if (!isset($_GET['session']) && !isset($_GET['date'])) halt(404);

$date = null;
try {
  $date = new DateTime($_GET['date'], new DateTimeZone('Europe/London'));
} catch (Exception $e) {
  halt(404);
}

// Get session
$getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay`, `MaxPlaces`, `AllSquads`, `RegisterGenerated`, `BookingOpens`, `BookingFee` FROM `sessionsBookable` INNER JOIN `sessions` ON sessionsBookable.Session = sessions.SessionID INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
$getSession->execute([
  $_GET['session'],
  $date->format('Y-m-d'),
  $tenant->getId(),
  $date->format('Y-m-d'),
  $date->format('Y-m-d'),
]);

$session = $getSession->fetch(PDO::FETCH_ASSOC);

if (!$session) {
  halt(404);
}

// Validate session happens on this day
$dayOfWeek = $date->format('w');

if ($session['SessionDay'] != $dayOfWeek) {
  halt(404);
}

$numFormatter = new NumberFormatter("en-GB", NumberFormatter::SPELLOUT);

// Number booked in
$getBookedCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ?");
$getBookedCount->execute([
  $session['SessionID'],
  $date->format('Y-m-d'),
]);
$bookedCount = $getBookedCount->fetchColumn();

$sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
$startTime = new DateTime($session['StartTime'], new DateTimeZone('UTC'));
$endTime = new DateTime($session['EndTime'], new DateTimeZone('UTC'));
$duration = $startTime->diff($endTime);
$hours = (int) $duration->format('%h');
$mins = (int) $duration->format('%i');

$sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));

$bookingCloses = clone $sessionDateTime;
$bookingCloses->modify('-15 minutes');

$now = new DateTime('now', new DateTimeZone('Europe/London'));

$bookingClosed = $now > $bookingCloses || bool($session['RegisterGenerated']);

$getCoaches = $db->prepare("SELECT Forename fn, Surname sn, coaches.Type code FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE coaches.Squad = ? ORDER BY coaches.Type ASC, Forename ASC, Surname ASC");

$getSessionSquads = $db->prepare("SELECT SquadName, ForAllMembers, SquadID FROM `sessionsSquads` INNER JOIN `squads` ON sessionsSquads.Squad = squads.SquadID WHERE sessionsSquads.Session = ? ORDER BY SquadFee DESC, SquadName ASC;");
$getSessionSquads->execute([
  $session['SessionID'],
]);
$squadNames = $getSessionSquads->fetchAll(PDO::FETCH_ASSOC);

$theTitle = 'Book ' . $session['SessionName'] . ' at ' . $startTime->format('H:i') . ' on ' . $date->format('j F Y') . ' - ' . $tenant->getName();
$theLink = autoUrl('sessions/booking/book?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($date->format('Y-m-d')));

$bookingOpensTime = null;
$bookingOpen = true;
if ($session['BookingOpens']) {
  try {
    $bookingOpensTime = new DateTime($session['BookingOpens'], new DateTimeZone('UTC'));
    $bookingOpensTime->setTimezone(new DateTimeZone('Europe/London'));
    if ($bookingOpensTime > $now) {
      $bookingOpen = false;
    }
  } catch (Exception $e) {
    // Ignore
  }
}

$socketDataInit = 'https://production-apis.tenant-services.membership.myswimmingclub.uk';
if (bool(getenv("IS_DEV"))) {
  $socketDataInit = 'https://apis.tenant-services.membership.myswimmingclub.uk';
}

$pagetitle = 'Session Booking';
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable')) ?>">Timetable</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable/booking')) ?>">Booking</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($date->format('Y-m-d')) ?>-S<?= htmlspecialchars($session['SessionID']) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($session['SessionName']) ?> on <?= htmlspecialchars($date->format('l j')) ?><sup><?= htmlspecialchars($date->format('S')) ?></sup> <?= htmlspecialchars($date->format('F Y')) ?>
        </h1>
        <p class="lead mb-0">
          <?php if ($session['MaxPlaces']) { ?>There are <?= htmlspecialchars($numFormatter->format($session['MaxPlaces'])) ?> places at this session<?php } else { ?>There are unlimited places at this session<?php } ?>
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <div class="col text-lg-end">
        <div class="btn-group">
          <?php if (($user->hasPermission('Admin') || $user->hasPermission('Coach')) && !$bookingClosed) { ?>
            <a href="<?= htmlspecialchars(autoUrl('sessions/booking/edit?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($date->format('Y-m-d')))) ?>" class="btn btn-primary">
              Edit booking settings
            </a>
          <?php } ?>
          <button class="btn btn-dark-l btn-outline-light-d" id="share-this" data-share-url="<?= htmlspecialchars($theLink) ?>" data-share-title="<?= htmlspecialchars($theTitle) ?>" data-share-text="<?= htmlspecialchars('Book a space for ' . $session['SessionName'] . ' at ' . $startTime->format('H:i') . ' on ' . $date->format('j F Y') . ' - ' . $tenant->getName()) ?>">
            Share <i class="fa fa-share" aria-hidden="true"></i>
          </button>
        </div>
        <?php if (($user->hasPermission('Admin') || $user->hasPermission('Coach')) && !$bookingClosed) { ?>
          <div class="mb-3"></div>
          <a href="<?= htmlspecialchars(autoUrl('sessions/booking/book-on-behalf-of?session=' . urlencode($session['SessionID']) . '&date=' . urlencode($date->format('Y-m-d')))) ?>" class="btn btn-primary">
            Book on behalf of
          </a>
        <?php } ?>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8 order-2 order-lg-1 mb-3">
      <p class="lead d-none d-lg-block">
        <span class="place-numbers-places-booked-string uc-first"><?= htmlspecialchars(mb_ucfirst($numFormatter->format($bookedCount))) ?></span> <span id="place-numbers-booked-places-member-string"><?php if ($bookedCount == 1) { ?>member has<?php } else { ?>members have<?php } ?></span> booked onto this session. <?php if ($session['MaxPlaces']) { ?><span class="place-numbers-places-remaining-string uc-first"><?= htmlspecialchars(mb_ucfirst($numFormatter->format($session['MaxPlaces'] - $bookedCount))) ?></span> <span id="place-numbers-places-remaining-member-string"><?php if (($session['MaxPlaces'] - $bookedCount) == 1) { ?>place remains<?php } else { ?>places remain<?php } ?></span> available.<?php } ?>
      </p>

      <?php if ($bookingClosed) { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>Booking has closed for this session</strong>
          </p>
          <p class="mb-0">
            Booking closed automatically at <?= htmlspecialchars($bookingCloses->format('H:i, j F Y (T)')) ?>, 15 minutes prior to the published session start time of <?= htmlspecialchars($sessionDateTime->format('H:i T')) ?>.
          </p>
        </div>
      <?php } ?>

      <!--  -->
      <div id="my-member-booking-container-box">
        <?= getMySessionBookingMembers($session, $date) ?>
      </div>

      <?php if ($user->hasPermission('Admin') || $user->hasPermission('Coach')) { ?>
        <div id="all-member-booking-container-box">
          <?= getAllBookedMembersForSession($session, $date) ?>
        </div>
      <?php } ?>

    </div>
    <div class="col order-1 order-lg-2">
      <div class="position-sticky top-3 mb-3">
        <div class="card card-body d-none d-lg-flex">
          <h2>Session Details</h2>
          <dl class="row mb-0">
            <dt class="col-sm-12">Date</dt>
            <dd class="col-sm-12"><?= htmlspecialchars($sessionDateTime->format('l j F Y')) ?></dd>

            <dt class="col-sm-12">Starts at</dt>
            <dd class="col-sm-12"><?= htmlspecialchars($startTime->format('H:i')) ?></dd>

            <dt class="col-sm-12">Ends at</dt>
            <dd class="col-sm-12"><?= htmlspecialchars($endTime->format('H:i')) ?></dd>

            <dt class="col-sm-12">Duration</dt>
            <dd class="col-sm-12"><?php if ($hours > 0) { ?><?= $hours ?> hour<?php if ($hours > 1) { ?>s<?php } ?> <?php } ?><?php if ($mins > 0) { ?><?= $mins ?> minute<?php if ($mins > 1) { ?>s<?php } ?><?php } ?></dd>

            <dt class="col-sm-12">Price per place</dt>
            <dd class="col-sm-12">&pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $session['BookingFee']))->withPointMovedLeft(2)->toScale(2)) ?></dd>

            <?php if ($bookingOpensTime) { ?>
              <dt class="col-sm-12">Booking opens at</dt>
              <dd class="col-sm-12"><?= htmlspecialchars($bookingOpensTime->format('H:i, j F Y')) ?></dd>
            <?php } ?>

            <?php if ($session['MaxPlaces']) { ?>
              <dt class="col-sm-12">Total places available</dt>
              <dd class="col-sm-12 place-numbers-max-places-int"><?= htmlspecialchars($session['MaxPlaces']) ?></dd>
            <?php } ?>

            <dt class="col-sm-12">Places booked</dt>
            <dd class="col-sm-12 place-numbers-places-booked-int"><?= htmlspecialchars($bookedCount) ?></dd>

            <?php if ($session['MaxPlaces']) { ?>
              <dt class="col-sm-12">Places remaining</dt>
              <dd class="col-sm-12 place-numbers-places-remaining-int"><?= htmlspecialchars(($session['MaxPlaces'] - $bookedCount)) ?></dd>
            <?php } ?>

            <?php for ($i = 0; $i < sizeof($squadNames); $i++) {
              $getCoaches->execute([
                $squadNames[$i]['SquadID'],
              ]);
              $coaches = $getCoaches->fetchAll(PDO::FETCH_ASSOC);
            ?>
              <dt class="col-sm-12"><?= htmlspecialchars($squadNames[$i]['SquadName']) ?> Coach<?php if (sizeof($coaches) > 0) { ?>es<?php } ?></dt>
              <dd class="col-sm-12">
                <ul class="list-unstyled mb-0">
                  <?php for ($y = 0; $y < sizeof($coaches); $y++) { ?>
                    <li><strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($coaches[$y]['fn'], $coaches[$y]['sn'])) ?></strong>, <?= htmlspecialchars(coachTypeDescription($coaches[$y]['code'])) ?></li>
                  <?php } ?>
                  <?php if (sizeof($coaches) == 0) { ?>
                    <li>None assigned</li>
                  <?php } ?>
                </ul>
              </dd>
            <?php } ?>

            <dt class="col-sm-12">Location</dt>
            <dd class="col-sm-12"><?= htmlspecialchars($session['VenueName']) ?>, <em><?= htmlspecialchars($session['Location']) ?></em></dd>

            <dt class="col-sm-12">Session Unique ID</dt>
            <dd class="col-sm-12 mb-0"><?= htmlspecialchars($date->format('Y-m-d')) ?>-S<?= htmlspecialchars($session['SessionID']) ?></dd>
          </dl>
        </div>
        <div class="d-block d-lg-none">
          <h2>Session Details</h2>
          <dl class="row mb-0">
            <dt class="col-sm-3">Date</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($sessionDateTime->format('l j F Y')) ?></dd>

            <dt class="col-sm-3">Starts at</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($startTime->format('H:i')) ?></dd>

            <dt class="col-sm-3">Ends at</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($endTime->format('H:i')) ?></dd>

            <dt class="col-sm-3">Duration</dt>
            <dd class="col-sm-9"><?php if ($hours > 0) { ?><?= $hours ?> hour<?php if ($hours > 1) { ?>s<?php } ?> <?php } ?><?php if ($mins > 0) { ?><?= $mins ?> minute<?php if ($mins > 1) { ?>s<?php } ?><?php } ?></dd>

            <dt class="col-sm-3">Price per place</dt>
            <dd class="col-sm-9">&pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $session['BookingFee']))->withPointMovedLeft(2)->toScale(2)) ?></dd>

            <?php if ($bookingOpensTime) { ?>
              <dt class="col-sm-3">Booking opens at</dt>
              <dd class="col-sm-9"><?= htmlspecialchars($bookingOpensTime->format('H:i, j F Y')) ?></dd>
            <?php } ?>

            <?php if ($session['MaxPlaces']) { ?>
              <dt class="col-sm-3">Total places available</dt>
              <dd class="col-sm-9 place-numbers-max-places-int"><?= htmlspecialchars($session['MaxPlaces']) ?></dd>
            <?php } ?>

            <dt class="col-sm-3">Places booked</dt>
            <dd class="col-sm-9 place-numbers-places-booked-int"><?= htmlspecialchars($bookedCount) ?></dd>

            <?php if ($session['MaxPlaces']) { ?>
              <dt class="col-sm-3">Places remaining</dt>
              <dd class="col-sm-9 place-numbers-places-remaining-int"><?= htmlspecialchars(($session['MaxPlaces'] - $bookedCount)) ?></dd>
            <?php } ?>

            <?php for ($i = 0; $i < sizeof($squadNames); $i++) {
              $getCoaches->execute([
                $squadNames[$i]['SquadID'],
              ]);
              $coaches = $getCoaches->fetchAll(PDO::FETCH_ASSOC);
            ?>
              <dt class="col-sm-3"><?= htmlspecialchars($squadNames[$i]['SquadName']) ?> Coach<?php if (sizeof($coaches) > 0) { ?>es<?php } ?></dt>
              <dd class="col-sm-9">
                <ul class="list-unstyled mb-0">
                  <?php for ($y = 0; $y < sizeof($coaches); $y++) { ?>
                    <li><strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($coaches[$y]['fn'], $coaches[$y]['sn'])) ?></strong>, <?= htmlspecialchars(coachTypeDescription($coaches[$y]['code'])) ?></li>
                  <?php } ?>
                  <?php if (sizeof($coaches) == 0) { ?>
                    <li>None assigned</li>
                  <?php } ?>
                </ul>
              </dd>
            <?php } ?>

            <dt class="col-sm-3">Location</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($session['VenueName']) ?>, <em><?= htmlspecialchars($session['Location']) ?></em></dd>

            <dt class="col-sm-3">Session Unique ID</dt>
            <dd class="col-sm-9 mb-0"><?= htmlspecialchars($date->format('Y-m-d')) ?>-S<?= htmlspecialchars($session['SessionID']) ?></dd>
          </dl>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="modal" id="booking-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="booking-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title" id="booking-modal-title">Confirm booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body">
        <form id="member-booking-form">
          <p>
            Please confirm you're booking <strong><span id="booking-modal-member-name"></span></strong> onto <strong><span id="booking-modal-session-name"></span></strong>
          </p>

          <input type="hidden" name="member-id" id="member-id" value="">
          <input type="hidden" name="session-id" id="session-id" value="">
          <input type="hidden" name="session-date" id="session-date" value="">

          <dl class="row">
            <dt class="col-md-4">Charge</dt>
            <dd class="col-md-8">&pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $session['BookingFee']))->withPointMovedLeft(2)->toScale(2)) ?></dd>

            <dt class="col-md-4">Location</dt>
            <dd class="col-md-8" id="booking-modal-session-location"><?= htmlspecialchars($session['VenueName']) ?>, <em><?= htmlspecialchars($session['Location']) ?></em></dd>
          </dl>

          <p class="mb-0">
            This is a new feature and we have not yet added a cancellation facility. Booking a place is final and your club may have penalties for non-attendance at a booked session.
          </p>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Don't book</button>
        <button type="submit" class="btn btn-info" form="member-booking-form" id="accept">Confirm booking</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="cancel-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="cancel-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title" id="cancel-modal-title">Cancel booking?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body">
        <form id="cancel-booking-form">
          <p>
            Please confirm you're cancelling <strong><span id="cancel-modal-member-name"></span></strong>'s booking for <strong><span id="cancel-modal-session-name"></span></strong>
          </p>

          <input type="hidden" name="member-id" id="cancel-member-id" value="">
          <input type="hidden" name="session-id" id="cancel-session-id" value="">
          <input type="hidden" name="session-date" id="cancel-session-date" value="">

          <dl class="row">
            <dt class="col-md-4">Charge</dt>
            <dd class="col-md-8">&pound;<?= htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $session['BookingFee']))->withPointMovedLeft(2)->toScale(2)) ?> (Charges are only added to accounts when the session register is generated. This means no refund credits need will be applied if you cancel a place now.)</dd>

            <dt class="col-md-4">Location</dt>
            <dd class="col-md-8" id="cancel-modal-session-location"><?= htmlspecialchars($session['VenueName']) ?>, <em><?= htmlspecialchars($session['Location']) ?></em></dd>
          </dl>

          <p class="mb-0">
            We will send an automatic email informing the member their booking has been cancelled.
          </p>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Don't cancel</button>
        <button type="submit" class="btn btn-danger" form="cancel-booking-form" id="accept">Cancel booking</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="sharing-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="sharing-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sharing-modal-title">Share this</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body">

        <div class="row gx-0 sharing">
          <div class="col">
            <div class="d-grid gap-2">
              <a target="_blank" class="btn btn-dark-l btn-outline-light-d dismiss-share-box" href="mailto:?subject=<?= rawurlencode($theTitle); ?>&body=<?= rawurlencode($theLink); ?>"><i class="fa  fa-envelope" aria-hidden="true"></i><span class="visually-hidden visually-hidden-focusable">Share by Email</span></a>
            </div>
          </div>

          <div class="col">
            <div class="d-grid gap-2">
              <a target="_self" class="btn btn-dark-l btn-outline-light-d" id="print-this-page"><i class="fa fa-print" aria-hidden="true"></i><span class="visually-hidden visually-hidden-focusable">Print</span></a>
            </div>
          </div>

          <div class="col">
            <div class="d-grid gap-2">
              <a target="_blank" class="btn btn-fb dismiss-share-box" href="http://www.facebook.com/sharer.php?u=<?= rawurlencode($theLink); ?>&amp;t=<?= ($theTitle); ?>"><i class="fa fa-facebook" aria-hidden="true"></i><span class="visually-hidden visually-hidden-focusable">Share on Facebook</span></a>
            </div>
          </div>

          <div class="col">
            <div class="d-grid gap-2">
              <a target="_blank" class="btn btn-tweet dismiss-share-box" href="https://twitter.com/intent/tweet?text=<?= rawurlencode($theTitle); ?>&url=<?= rawurlencode($theLink); ?>"><i class="fa fa-twitter" aria-hidden="true"></i><span class="visually-hidden visually-hidden-focusable">Share on Twitter</span></a>
            </div>
          </div>

          <div class="col">
            <div class="d-grid gap-2">
              <a target="_blank" class="btn btn-whatsapp dismiss-share-box" href="https://wa.me/?text=<?= rawurlencode($theLink); ?>" data-action="share/whatsapp/share"><i class="fa fa-whatsapp" aria-hidden="true"></i><span class="visually-hidden visually-hidden-focusable">Share with Whatsapp</span></a>
            </div>
          </div>

          <div class="col">
            <div class="d-grid gap-2">
              <a target="_blank" class="btn btn-linkedin dismiss-share-box" href="https://www.linkedin.com/shareArticle?mini=true&url=<?= rawurlencode($theLink); ?>&title=<?= rawurlencode($theTitle); ?>&source=<?= rawurlencode($tenant->getName() . ' / SCDS Membership') ?>"><i class="fa fa-linkedin" aria-hidden="true"></i><span class="visually-hidden visually-hidden-focusable">Share on Linked In</span></a>
            </div>
          </div>
        </div>

        <p class="small mt-3 mb-0"><?= htmlspecialchars($tenant->getName()) ?> is not responsible for these services</p>

      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Don't cancel</button>
        <button type="submit" class="btn btn-danger" form="cancel-booking-form" id="accept">Cancel booking</button>
      </div> -->
    </div>
  </div>
</div>

<div id="ajaxData" data-booking-ajax-url="<?= htmlspecialchars(autoUrl('sessions/booking/book')) ?>" data-cancellation-ajax-url="<?= htmlspecialchars(autoUrl('sessions/booking/cancel')) ?>" data-my-member-reload-ajax-url="<?= htmlspecialchars(autoUrl('sessions/booking/my-booking-info?session=' . urlencode($_GET['session']) . '&date=' . urlencode($_GET['date']))) ?>" data-all-member-reload-ajax-url="<?= htmlspecialchars(autoUrl('sessions/booking/all-booking-info?session=' . urlencode($_GET['session']) . '&date=' . urlencode($_GET['date']))) ?>" data-socket-init="<?= htmlspecialchars($socketDataInit) ?>" data-socket-room-name="<?= htmlspecialchars('session_booking_room:' . $date->format('Y-m-d') . '-S' . $session['SessionID']) ?>"></div>

<?php

$footer = new \SCDS\Footer();
if (bool(getenv("IS_DEV"))) {
  $footer->addExternalJs('https://apis.tenant-services.membership.myswimmingclub.uk/socket.io/socket.io.js');
} else {
  $footer->addExternalJs('https://production-apis.tenant-services.membership.myswimmingclub.uk/socket.io/socket.io.js');
}
$footer->addJs('public/js/attendance/booking/book-session.js');
$footer->render();
