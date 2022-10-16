<?php

function getMySessionBookingMembers($session, $date)
{

  $db = app()->db;
  $tenant = app()->tenant;
  $user = app()->user;

  // Get bookable members
  $members = [];
  if (bool($session['AllSquads'])) {
    $getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members WHERE UserID = ? ORDER BY fn ASC, sn ASC");
    $getMembers->execute([
      $user->getId(),
    ]);
    $members = $getMembers->fetchAll(PDO::FETCH_ASSOC);
  } else {
    // Get session squads
    $getSquads = $db->prepare("SELECT `Squad` FROM `sessionsSquads` WHERE `Session` = ?");
    $getSquads->execute([
      $session['SessionID'],
    ]);

    while ($squad = $getSquads->fetchColumn()) {
      // Get members for this user in this squad
      $getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members INNER JOIN squadMembers ON members.MemberID = squadMembers.Member WHERE UserID = ? AND squadMembers.Squad = ? ORDER BY fn ASC, sn ASC");
      $getMembers->execute([
        $user->getId(),
        $squad,
      ]);

      while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {
        $members[] = $member;
      }
    }

    // Remove duplicated
    // $members = array_unique($members);
    $members = array_map("unserialize", array_unique(array_map("serialize", $members)));
  }

  $getBooking = $db->prepare("SELECT `BookedAt` FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ? AND `Member` = ?");

  // Check there is space
  $getBookedCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookings` WHERE `Session` = ? AND `Date` = ?");
  $getBookedCount->execute([
    $session['SessionID'],
    $date->format('Y-m-d'),
  ]);
  $bookedCount = $getBookedCount->fetchColumn();

  $spaces = PHP_INT_MAX;
  if ($session['MaxPlaces']) {
    $spaces = $session['MaxPlaces'];
  }

  $spaces = $spaces - $bookedCount;

  $sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
  $bookingCloses = clone $sessionDateTime;
  $bookingCloses->modify('-15 minutes');

  $now = new DateTime('now', new DateTimeZone('Europe/London'));

  $bookingClosed = $now > $bookingCloses;

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

?>

  <h2>Book</h2>
  <p class="lead">
    Book a place for a member linked to your account.
  </p>

  <?php if (sizeof($members) > 0) { ?>

    <?php if (sizeof($members) > 1 && sizeof($members) > $spaces) { ?>
      <p>
        <strong>Beware:</strong> There is not enough space to book all of your members onto this session.
      </p>
    <?php } else if ($spaces < 1) { ?>
      <p>
        We're sorry but there are no spaces left to book on this session.
      </p>
    <?php } ?>

    <ul class="list-group mb-3" id="member-booking-list">
      <?php foreach ($members as $member) {

        // Check if booked
        $getBooking->execute([
          $session['SessionID'],
          $date->format('Y-m-d'),
          $member['id'],
        ]);
        $booking = $getBooking->fetch(PDO::FETCH_ASSOC);

        $bookingTime = null;
        if ($booking) {
          $bookingTime = new DateTime($booking['BookedAt'], new DateTimeZone('UTC'));
          $bookingTime->setTimezone(new DateTimeZone('Europe/London'));
        }

      ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <span class="d-block"><strong><a href="<?= htmlspecialchars(autoUrl('members/' . $member['id'])) ?>"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?></a></strong></span>
            <?php if ($booking) { ?></strong><span class="d-block">Booked at <?= htmlspecialchars($bookingTime->format('H:i, j F Y')) ?></span><?php } ?>
          </span>
          <?php if ($bookingClosed && $booking) { ?>
            <!-- <span class="text-muted">Booking closed</span> -->
          <?php } else if ($bookingClosed) { ?>
            <span class="text-muted">Booking closed</span>
          <?php } else { ?>
            <?php if ($bookingOpen && $spaces > 0 && !$booking) { ?>
              <span>
                <button class="btn btn-primary" type="button" data-member-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?>" data-member-id="<?= htmlspecialchars($member['id']) ?>" data-operation="book-place" data-session-id="<?= htmlspecialchars($session['SessionID']) ?>" data-session-name="<?= htmlspecialchars($session['SessionName']) ?> on <?= htmlspecialchars($date->format('j F Y')) ?>" data-session-location="<?= htmlspecialchars($session['Location']) ?>" data-session-date="<?= htmlspecialchars($date->format('Y-m-d')) ?>">Book</button>
              </span>
            <?php } else if (!$bookingOpen && $bookingOpensTime) { ?>
              <span class="text-muted small">
                Booking opens at <?= htmlspecialchars($bookingOpensTime->format('H:i T, d/m/Y')) ?>
              </span>
            <?php } else if ($spaces < 1) { ?>
              <span>
                Fully booked. No spaces.
              </span>
            <?php } ?>
          <?php } ?>
        </li>
      <?php } ?>
    </ul>

  <?php } else { ?>
    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>You have no members</strong>
      </p>
      <p class="mb-0">
        Only members can be booked onto sessions.
      </p>
    </div>
<?php }
}
