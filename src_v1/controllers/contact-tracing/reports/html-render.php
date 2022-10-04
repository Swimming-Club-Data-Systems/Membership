<?php

use function GuzzleHttp\json_decode;

$json = json_decode($json);

$pagetitle = htmlspecialchars($json->location->name) . ' Report';
include BASE_PATH . 'views/header.php';

$fromDate = new DateTime($json->from, new DateTimeZone('UTC'));
$fromDate->setTimezone(new DateTimeZone('Europe/London'));

$toDate = new DateTime($json->to, new DateTimeZone('UTC'));
$toDate->setTimezone(new DateTimeZone('Europe/London'));

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing/reports')) ?>">Reports</a></li>
        <!-- <li class="breadcrumb-item active" aria-current="page">Locations</li> -->
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          <?= htmlspecialchars($json->location->name) ?>
        </h1>
        <p class="lead mb-0">
          <?= htmlspecialchars($fromDate->format('H:i j F Y')) ?> to <?= htmlspecialchars($toDate->format('H:i j F Y')) ?>
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col">
      <h2 class="mb-3">Visitors</h2>

      <?php if (sizeof($json->visitors) > 0) { ?>
        <ul class="list-group">
          <?php for ($i = 0; $i < sizeof($json->visitors); $i++) { ?>
            <?php
            $visitTime = new DateTime($json->visitors[$i]->time, new DateTimeZone('UTC'));
            $visitTime->setTimezone(new DateTimeZone('Europe/London'));
            ?>
            <li class="list-group-item">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <p class="mb-0">
                    <?php if ($json->visitors[$i]->type == 'user') { ?>
                      <a target="_blank" href="<?= htmlspecialchars(autoUrl('users/' . $json->visitors[$i]->user)) ?>">
                      <?php } ?>
                      <?php if ($json->visitors[$i]->type == 'member') { ?>
                        <a target="_blank" href="<?= htmlspecialchars(autoUrl('members/' . $json->visitors[$i]->member)) ?>">
                        <?php } ?>
                        <strong><?= htmlspecialchars($json->visitors[$i]->name) ?></strong>
                        <?php if ($json->visitors[$i]->type == 'user' || $json->visitors[$i]->type == 'member') { ?>
                        </a>
                      <?php } ?>
                  </p>
                  <p class="mb-0">
                    <?= htmlspecialchars($visitTime->format('H:i \\o\n j F Y')) ?>
                  </p>
                  <div class="d-md-none mb-3"></div>
                </div>
                <div class="col">
                  <?php
                  try {
                    if (!isset($json->visitors[$i]->phone)) {
                      throw new Exception('Can\'t parse number');
                    }
                    $number = \Brick\PhoneNumber\PhoneNumber::parse((string) $json->visitors[$i]->phone);
                  ?>
                    <div class="d-grid gap-2">
                      <a href="<?= htmlspecialchars($number->format(\Brick\PhoneNumber\PhoneNumberFormat::RFC3966)) ?>" class="btn btn-dark-l btn-outline-light-d">
                        <i class="fa fa-phone" aria-hidden="true"></i> <?= htmlspecialchars($number->formatForCallingFrom('GB')) ?>
                      </a>
                    </div>
                  <?php
                  } catch (\Brick\PhoneNumber\PhoneNumberParseException $e) {
                    // Do nothing
                  }
                  ?>
                </div>
              </div>
            </li>
          <?php } ?>
        </ul>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>There were no visitors to this location in the selected timeframe</strong>
          </p>
          <p class="mb-0">
            Try modifying your selection.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
