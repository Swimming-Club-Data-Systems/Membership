<?php

function getAllBookedMembersForSession($session, $date)
{

  $db = app()->db;
  $tenant = app()->tenant;
  $user = app()->user;

  $getBookedMembers = null;
  $getBookedMembers = $db->prepare("SELECT Member id, MForename fn, MSurname sn, BookedAt FROM sessionsBookings INNER JOIN members ON sessionsBookings.Member = members.MemberID WHERE sessionsBookings.Session = ? AND sessionsBookings.Date = ? ORDER BY BookedAt ASC, MForename ASC, MSurname ASC;");
  $getBookedMembers->execute([
    $session['SessionID'],
    $date->format('Y-m-d'),
  ]);

  $bookingNumber = 1;

  $sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $session['StartTime'], new DateTimeZone('Europe/London'));
  $bookingCloses = clone $sessionDateTime;
  $bookingCloses->modify('-15 minutes');

  $now = new DateTime('now', new DateTimeZone('Europe/London'));

  $bookingClosed = $now > $bookingCloses;

?>

  <h2>Booked members</h2>
  <p class="lead">
    Members who have booked a place for this session.
  </p>

  <?php if ($bookedMember = $getBookedMembers->fetch(PDO::FETCH_ASSOC)) { ?>
    <ol class="list-group user-select-none" id="all-member-booking-list">
      <?php do {
        $booked = new DateTime($bookedMember['BookedAt'], new DateTimeZone('UTC'));
        $booked->setTimezone(new DateTimeZone('Europe/London'));
      ?>
        <li class="list-group-item" id="<?= htmlspecialchars('member-' . $bookedMember['id'] . '-booking') ?>">
          <div class="row align-items-center">
            <div class="col">
              <div>
                <a class="font-weight-bold" href="<?= htmlspecialchars(autoUrl('members/' . $bookedMember['id'])) ?>">
                  <?= htmlspecialchars(\SCDS\Formatting\Names::format($bookedMember['fn'], $bookedMember['sn'])) ?>
                </a>
              </div>
              <div>
                <em>Booked at <?= htmlspecialchars($booked->format('H:i, j F Y')) ?>, (Booking Position #<?= htmlspecialchars($bookingNumber) ?>)</em>
              </div>
            </div>
            <div class="col-auto">
              <?php if ($bookingClosed) { ?>
                <span class="text-muted">Booking closed</span>
              <?php } else { ?>
                <button class="btn btn-danger" type="button" data-member-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($bookedMember['fn'], $bookedMember['sn'])) ?>" data-member-id="<?= htmlspecialchars($bookedMember['id']) ?>" data-operation="cancel-place" data-session-id="<?= htmlspecialchars($session['SessionID']) ?>" data-session-name="<?= htmlspecialchars($session['SessionName']) ?> on <?= htmlspecialchars($date->format('j F Y')) ?>" data-session-location="<?= htmlspecialchars($session['Location']) ?>" data-session-date="<?= htmlspecialchars($date->format('Y-m-d')) ?>">Remove</button>
              <?php } ?>
            </div>
          </div>
        </li>
        <?php $bookingNumber++; ?>
      <?php } while ($bookedMember = $getBookedMembers->fetch(PDO::FETCH_ASSOC)); ?>
    </ol>
  <?php } else { ?>
    <div class="alert alert-info">
      <p class="mb-0">
        <strong>There are no members booked on this session yet</strong>
      </p>
    </div>
<?php }
}
