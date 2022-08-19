<?php

function registerSheetGenerator($date, $sessionId)
{
  $db = app()->db;
  $user = app()->user;

  $photoPermissionDescriptions = [
    'Website' => 'Take photos of this member for our website',
    'Social' => 'Take photos of this member for our social media',
    'Noticeboard' => 'Take photos of this member for our noticeboard',
    'FilmTraining' => 'Film this member for the purposes of training',
    'ProPhoto' => 'Take professional photographs of this member',
  ];

  try {

    // Get session
    $session = TrainingSession::get($sessionId);

    if ($session->getDayOfWeekInt() != (int) $date->format('w')) {
      throw new Exception('Invalid');
    }

    $squads = $session->getSquads();

    $register = $session->getRegister($date->format('Y-m-d'));

    $weekId = $session->getWeekId($date->format('Y-m-d'));

    // Work out if this session must be booked
    $getSession = $db->prepare("SELECT `SessionID`, `SessionName`, `DisplayFrom`, `DisplayUntil`, `StartTime`, `EndTime`, `VenueName`, `Location`, `SessionDay`, `MaxPlaces`, `AllSquads` FROM `sessionsBookable` INNER JOIN `sessions` ON sessionsBookable.Session = sessions.SessionID INNER JOIN `sessionsVenues` ON `sessions`.`VenueID` = `sessionsVenues`.`VenueID` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ? AND DisplayFrom <= ? AND DisplayUntil >= ?");
    $getSession->execute([
      $sessionId,
      $date->format('Y-m-d'),
      app()->tenant->getId(),
      $date->format('Y-m-d'),
      $date->format('Y-m-d'),
    ]);

    $bookingSessionInfo = $getSession->fetch(PDO::FETCH_ASSOC);
    $bookingClosed = false;
    $bookingCloses = null;
    if ($bookingSessionInfo) {
      $sessionDateTime = DateTime::createFromFormat('Y-m-d-H:i:s', $date->format('Y-m-d') .  '-' . $bookingSessionInfo['StartTime'], new DateTimeZone('Europe/London'));
      $bookingCloses = clone $sessionDateTime;
      $bookingCloses->modify('-15 minutes');

      $now = new DateTime('now', new DateTimeZone('Europe/London'));

      $bookingClosed = $now > $bookingCloses;
    }

    $markdown = new ParsedownExtra();
    $markdown->setSafeMode(true);

    $getLatestCovidSurveyCompletion = $db->prepare("SELECT `ID`, `DateTime`, `OfficerApproval`, `ApprovedBy`, `Forename`, `Surname` FROM covidHealthScreen LEFT JOIN users ON covidHealthScreen.ApprovedBy = users.UserID WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

    $getLatestCovidRACompletion = $db->prepare("SELECT `ID`, `DateTime`, `MemberAgreement`, `Guardian`, `Forename`, `Surname` FROM covidRiskAwareness LEFT JOIN users ON users.UserID = covidRiskAwareness.Guardian WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

?>
    <form id="register-form">

      <input type="hidden" name="displayed-reg-date" id="displayed-reg-date" value="<?= htmlspecialchars($date->format("Y-m-d")) ?>">
      <input type="hidden" name="displayed-reg-session" id="displayed-reg-session" value="<?= htmlspecialchars($sessionId) ?>">
      <?= \SCDS\CSRF::write() ?>

      <div class="card mb-3">
        <div class="card-header">
          Take register
        </div>
        <div class="card-body">
          <p class="mb-0">
            <strong><?= htmlspecialchars($session->getName()) ?></strong> (<?= htmlspecialchars($session->getStartTime()->format('H:i')) ?> - <?= htmlspecialchars($session->getEndTime()->format('H:i')) ?>)
          </p>

          <p class="mb-0">
            <em><?php for ($i = 0; $i < sizeof($squads); $i++) { ?><?php if ($i > 0) { ?>, <?php } ?><?= htmlspecialchars($squads[$i]->getName()) ?><?php } ?></em>
          </p>

          <?php if ($bookingSessionInfo) { ?>
            <p class="mt-3 mb-0">
              This session requires pre-booking.
            </p>
          <?php } ?>

          <?php if (!$bookingClosed && $bookingSessionInfo) { ?>
            <div class="text-center card card-body mt-3">
              <p class="mb-0">
                <strong>Booking is still open for this session!</strong>
              </p>
              <p>
                We will generate the register for this session when booking closes at <?= htmlspecialchars($bookingCloses->format('H:i T')) ?>
              </p>

              <p class="mb-0">
                Until then, head to <a href="<?= htmlspecialchars(autoUrl('sessions/booking/book?session=' . urlencode($sessionId) . '&date=' . urlencode($date->format('Y-m-d')))) ?>">the booking page for this session</a> to see who's booked in.
              </p>
            </div>
          <?php } else if (sizeof($register) == 0) { ?>
            <div class="alert alert-warning mt-3 mb-0">
              <p class="mb-0">
                <strong>There are no members for this session</strong>
              </p>
            </div>
          <?php } ?>
        </div>
        <?php if (sizeof($register) > 0) { ?>
          <div id="socket-room-info" data-room-name="<?= htmlspecialchars('register_room:week-' . $weekId . '-session-' . $sessionId) ?>"></div>

          <ul class="list-group list-group-flush accordion">
            <?php foreach ($register as $row) { ?>
              <?php
              // COVID 19 STUFF
              $getLatestCovidSurveyCompletion->execute([
                $row['id']
              ]);
              $cvLatest = $getLatestCovidSurveyCompletion->fetch(PDO::FETCH_ASSOC);

              $getLatestCovidRACompletion->execute([
                $row['id'],
              ]);
              $cvRALatest = $getLatestCovidRACompletion->fetch(PDO::FETCH_ASSOC);
              // END OF COVID STUFF
              ?>
              <li class="list-group-item py-3">
                <div class="my-n3 py-3 sticky-top">
                  <div class="row align-items-center">
                    <div class="col">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input checkbox-input" id="member-<?= htmlspecialchars($row['id']) ?>" <?php if (bool($row['tick'])) { ?>checked<?php } ?> data-indeterminate="<?php if (bool($row['indeterminate'])) { ?>true<?php } else { ?>false<?php } ?>"" data-week-id=" <?= htmlspecialchars($row['week_id']) ?>" data-session-id="<?= htmlspecialchars($row['session_id']) ?>" data-member-id="<?= htmlspecialchars($row['id']) ?>">
                        <label class="form-check-label d-block" for="member-<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars(\SCDS\Formatting\Names::format($row['fn'], $row['sn'])) ?><?php if ($row['show_gender']) { ?><br><em><?= htmlspecialchars($row['gender_identity']) ?>, <?= htmlspecialchars($row['gender_pronouns']) ?></em><?php } ?></label>
                      </div>
                    </div>
                    <?php if (sizeof($row['medical']) > 0 || sizeof($row['photo']) > 0 || $row['notes'] || sizeof($row['contacts']) > 0) { ?>
                      <div class="col-auto">
                        <div class="<?php if (sizeof($row['medical']) > 0 || sizeof($row['photo']) > 0 || $row['notes'] || sizeof($row['contacts']) > 0) { ?>mb-1<?php } ?> text-end">
                          <?php if ($cvRALatest && bool($cvRALatest['MemberAgreement'])) { ?>
                            <span class="badge badge-sm bg-success">
                              RA <i class="fa fa-check-circle" aria-hidden="true"></i> <span class="visually-hidden">Valid declaration</span>
                            </span>
                          <?php } else { ?>
                            <span class="badge badge-sm bg-danger">
                              RA <i class="fa fa-times-circle" aria-hidden="true"></i> <span class="visually-hidden">form not submitted or new submission required</span>
                            </span>
                          <?php } ?>
                          <?php if ($cvLatest) { ?>
                            <?php if (bool($cvLatest['OfficerApproval'])) { ?>
                              <span class="badge badge-sm bg-success">
                                HS <i class="fa fa-check-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted and approved</span>
                              </span>
                            <?php } else if (!bool($cvLatest['OfficerApproval']) && $cvLatest['ApprovedBy']) { ?>
                              <span class="badge badge-sm bg-danger">
                                HS <i class="fa fa-times-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted and rejected</span>
                              </span>
                            <?php } else if (!bool($cvLatest['OfficerApproval']) && !$cvLatest['ApprovedBy']) { ?>
                              <span class="badge badge-sm bg-warning">
                                HS <i class="fa fa-minus-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted pending approval</span>
                              </span>
                            <?php } ?>
                          <?php } else { ?>
                            <span class="badge badge-sm bg-danger">
                              NO HS <span class="visually-hidden">Survey submitted</span>
                            </span>
                          <?php } ?>
                        </div>
                        <div>
                          <div class="btn-group d-flex">
                            <?php if (sizeof($row['medical']) > 0 || sizeof($row['photo']) > 0 || $row['notes']) { ?>
                              <button class="btn btn-sm btn-warning" type="button" data-show="extra-info-<?= htmlspecialchars($row['id']) ?>" aria-expanded="false" aria-controls="extra-info-<?= htmlspecialchars($row['id']) ?>"><span class="d-none d-md-inline">Medical, photo &amp; notes info</span><span class="d-md-none">Info</span> <span class="fa fa-caret-down"></span></button>
                            <?php } ?>
                            <?php if (sizeof($row['contacts']) > 0) { ?>
                              <button class="btn btn-sm btn-danger" type="button" data-show="emergency-contacts-<?= htmlspecialchars($row['id']) ?>" aria-expanded="false" aria-controls="emergency-contacts-<?= htmlspecialchars($row['id']) ?>"><span class="fa fa-phone"></span> <span class="fa fa-caret-down"></span></button>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                    <?php } ?>
                  </div>
                </div>
                <?php if (sizeof($row['contacts']) > 0) { ?>
                  <div class="d-block">
                    <div class="mb-n3 d-none register-hideable" id="emergency-contacts-<?= htmlspecialchars($row['id']) ?>">
                      <hr>
                      <p class="h5 mb-0">
                        Emergency contacts
                      </p>
                      <p>
                        In an emergency, dial one of the contact numbers shown below.
                      </p>

                      <div class="">
                        <div class="row">
                          <?php foreach ($row['contacts'] as $ec) { ?>

                            <div class="col-md-6 col-xl-4">
                              <div class="card card-body mb-2">
                                <div class="row align-items-center">
                                  <div class="col-12 text-center">
                                    <div class="text-truncate"><strong><?= htmlspecialchars($ec->getName()) ?></strong></div>
                                    <div class="text-truncate"><?= htmlspecialchars($ec->getRelation()) ?></div>
                                    <div class="mb-2"></div>
                                  </div>
                                  <div class="col">
                                    <div class="d-grid gap-2">
                                      <a href="<?= htmlspecialchars($ec->getRFCContactNumber()) ?>" class="btn btn-success">
                                        <i class="fa fa-phone" aria-hidden="true"></i> <?= htmlspecialchars($ec->getInternationalContactNumber()) ?>
                                      </a>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>

                          <?php } ?>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>
                <?php if (sizeof($row['medical']) > 0 || sizeof($row['photo']) > 0 || $row['notes']) { ?>
                  <div class="d-block">
                    <div class="mb-n3 d-none register-hideable" id="extra-info-<?= htmlspecialchars($row['id']) ?>">
                      <hr>
                      <?php if (sizeof($row['medical']) > 0) { ?>
                        <div class="">
                          <p class="h5 mb-3">
                            <strong>Medical</strong>
                          </p>

                          <div class="row">
                            <?php if (isset($row['medical']['Conditions'])) { ?>
                              <div class="col-md pb-3">
                                <div class="card card-body border-danger h-100">
                                  <p class="text-danger">
                                    <strong>
                                      Medical conditions
                                    </strong>
                                  </p>
                                  <div class="">
                                    <?= $markdown->text($row['medical']['Conditions']) ?>
                                  </div>
                                </div>
                              </div>
                            <?php } ?>

                            <?php if (isset($row['medical']['Allergies'])) { ?>
                              <div class="col-md pb-3">
                                <div class="card card-body border-danger h-100">
                                  <p class="text-danger">
                                    <strong>
                                      Allergies
                                    </strong>
                                  </p>
                                  <div class="">
                                    <?= $markdown->text($row['medical']['Allergies']) ?>
                                  </div>
                                </div>
                              </div>
                            <?php } ?>

                            <?php if (isset($row['medical']['Medication'])) { ?>
                              <div class="col-md pb-3">
                                <div class="card card-body border-danger h-100">
                                  <p class="text-danger">
                                    <strong>
                                      Medication
                                    </strong>
                                  </p>
                                  <div class="">
                                    <?= $markdown->text($row['medical']['Medication']) ?>
                                  </div>
                                </div>
                              </div>
                            <?php } ?>
                          </div>
                        </div>
                      <?php } ?>

                      <?php if (sizeof($row['photo']) > 0) { ?>
                        <div class="">
                          <p class="h5 mb-3">
                            <strong>Photo restrictions</strong>
                          </p>
                          <div class="row">
                            <div class="col-md pb-3">
                              <div class="card card-body border-danger h-100">
                                <p class="text-danger">
                                  <i class="fa fa-exclamation-circle" aria-hidden="true"></i> <strong>You must not</strong>
                                </p>

                                <ul class="list-unstyled mb-0">
                                  <?php foreach ($row['photo'] as $disallowed => $bool) { ?>
                                    <li><?= htmlspecialchars($photoPermissionDescriptions[$disallowed]) ?></li>
                                  <?php } ?>
                                </ul>
                              </div>
                              <div class="d-md-none mb-3"></div>
                            </div>

                            <?php if (sizeof($row['photo']) < 5) { ?>
                              <div class="col-md pb-3">
                                <div class="card card-body border-success h-100">
                                  <p class="text-success">
                                    <i class="fa fa-check-circle" aria-hidden="true"></i> <strong>You may</strong>
                                  </p>

                                  <ul class="list-unstyled mb-0">
                                    <?php foreach ($photoPermissionDescriptions as $key => $text) {
                                      if (!isset($row['photo'][$key])) { ?>
                                        <li><?= htmlspecialchars($text) ?></li>
                                    <?php }
                                    } ?>
                                  </ul>
                                </div>
                              </div>
                            <?php } ?>
                          </div>
                        </div>
                      <?php } ?>

                      <?php if ($row['notes']) { ?>
                        <div class="">
                          <p class="h5 mb-3">
                            <strong>Other notes</strong>
                          </p>
                          <div>
                            <?= $markdown->text($row['notes']) ?>
                          </div>
                        </div>
                      <?php } ?>
                    </div>
                  </div>
                <?php } ?>
              </li>
            <?php } ?>
          </ul>
        <?php } ?>
      </div>
    </form>

    <?php if (sizeof($register) > 0) { ?>
      <p>
        Register saves automatically
      </p>

      <?php if ($user->hasPermission('Admin')) { ?>
        <p>
          <a href="<?= htmlspecialchars(autoUrl('covid/contact-tracing/reports/from-register?date=' . urlencode($date->format('Y-m-d')) . '&session=' . urlencode($sessionId))) ?>">Generate COVID-19 Contact Details List</a> for this register.
        </p>
      <?php } ?>
    <?php } ?>
<?php

  } catch (Exception $e) {
    // pre($e);
  }
}
