<?php

$db = app()->db;
$tenant = app()->tenant;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

if (!isset($_GET['session']) || !isset($_GET['date'])) {
  halt(404);
}

$date = new DateTime($_GET['date'], new DateTimeZone('Europe/London'));
$now = new DateTime('now', new DateTimeZone('Europe/London'));

if ($date > $now) {
  halt(404);
}

try {

  // Get session
  $session = TrainingSession::get($_GET['session']);

  if ($session->getDayOfWeekInt() != (int) $date->format('w')) {
    throw new Exception('Invalid');
  }

  $squads = $session->getSquads();

  $register = $session->getRegister($date->format('Y-m-d'));

  $weekId = $session->getWeekId($date->format('Y-m-d'));

  $getContactDetails = $db->prepare("SELECT Mobile, EmailAddress FROM users WHERE UserID = ?");

  $venue = $session->getVenue();

  $pagetitle = 'Contact Details';

  include BASE_PATH . 'views/header.php';

?>

  <div class="bg-light mt-n3 py-3 mb-3">
    <div class="container-xl">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
          <li class="breadcrumb-item active" aria-current="page">Reports</li>
        </ol>
      </nav>

      <div class="row align-items-center">
        <div class="col-lg-12">
          <h1>
            Generate report <small><?= htmlspecialchars($venue->getName()) ?> via Attendance System</small>
          </h1>
          <p class="lead mb-0">
            <?= htmlspecialchars($session->getName()) ?></strong> (<?= htmlspecialchars($session->getStartTime()->format('H:i')) ?> - <?= htmlspecialchars($session->getEndTime()->format('H:i')) ?>, <?= htmlspecialchars($date->format('l j F Y')) ?>)<?php if (sizeof($squads) > 0) { ?>, <em><?php for ($i = 0; $i < sizeof($squads); $i++) { ?><?php if ($i > 0) { ?>, <?php } ?><?= htmlspecialchars($squads[$i]->getName()) ?><?php } ?></em><?php } ?>
          </p>
        </div>
      </div>

    </div>
  </div>

  <div class="container-xl">
    <?php if (sizeof($register) > 0) { ?>
      <div class="row">
        <div class="col">
          <ul class="list-group">
            <?php foreach ($register as $row) { ?>
              <?php
              $details = null;
              $number = null;
              if ($row['user']) {
                $getContactDetails->execute([
                  $row['user']
                ]);
                $details = $getContactDetails->fetch(PDO::FETCH_ASSOC);

                try {
                  $number = PhoneNumber::parse($details['Mobile']);
                } catch (PhoneNumberParseException $e) {
                  $number = false;
                }
              }
              ?>
              <li class="list-group-item <?php if (!$row['tick']) { ?>bg-light text-muted<?php } ?>">
                <div class="row">
                  <div class="col-md-6 col-lg-8">
                    <div class=" <?php if ($row['tick']) { ?>font-weight-bold<?php } ?>"><?= htmlspecialchars(\SCDS\Formatting\Names::format($row['fn'], $row['sn'])) ?></div>
                    <?php if (!$row['tick']) { ?>
                      <div class="small">Not present at this session</div>
                    <?php } ?>
                    <div class="mb-2 d-md-none"></div>
                  </div>
                  <div class="col">
                    <?php if ($details) { ?>

                      <dl class="row mb-0">
                        <dt class="col-4 col-lg-2">
                          Phone
                        </dt>
                        <dd class="col-8 col-lg-10">
                          <?php if ($number) { ?><a href="<?= htmlspecialchars($number->format(PhoneNumberFormat::RFC3966)) ?>"><?= htmlspecialchars($number->format(PhoneNumberFormat::INTERNATIONAL)) ?></a><?php } else { ?>None<?php } ?>
                        </dd>

                        <dt class="col-4 col-lg-2">
                          Email
                        </dt>
                        <dd class="col-8 col-lg-10 mb-0 text-truncate">
                          <?php if (mb_strlen($details['EmailAddress']) > 0) { ?><a href="<?= htmlspecialchars('mailto:' . $details['EmailAddress']) ?>"><?= htmlspecialchars($details['EmailAddress']) ?></a><?php } else { ?>None<?php } ?>
                        </dd>
                      </dl>

                    <?php } else { ?>
                      <div class="alert alert-warning mb-0">
                        <p class="mb-0">
                          <strong>No contact details on file</strong>
                        </p>
                      </div>
                    <?php } ?>
                  </div>
                </div>

              </li>
            <?php } ?>
          </ul>
        </div>
      </div>
    <?php } else { ?>

      <div class="row">
        <div class="col-lg-8">
          <div class="alert alert-info">
            <p class="mb-0">
              <strong>No register has been completed for this session yet, no members exist, or the session is in the future.</strong>
            </p>
            <p class="mb-0">
              Check back later.
            </p>
          </div>
        </div>
      </div>

    <?php } ?>
  </div>

<?php

  $footer = new \SCDS\Footer();
  $footer->render();
} catch (Exception $e) {
  halt(404);
}
