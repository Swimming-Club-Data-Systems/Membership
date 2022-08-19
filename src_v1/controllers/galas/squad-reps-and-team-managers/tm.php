<?php

require 'tm.json.php';
$data = json_decode($output);

$swimsArray = [
  '25Free' => '25&nbsp;Free',
  '50Free' => '50&nbsp;Free',
  '100Free' => '100&nbsp;Free',
  '200Free' => '200&nbsp;Free',
  '400Free' => '400&nbsp;Free',
  '800Free' => '800&nbsp;Free',
  '1500Free' => '1500&nbsp;Free',
  '25Back' => '25&nbsp;Back',
  '50Back' => '50&nbsp;Back',
  '100Back' => '100&nbsp;Back',
  '200Back' => '200&nbsp;Back',
  '25Breast' => '25&nbsp;Breast',
  '50Breast' => '50&nbsp;Breast',
  '100Breast' => '100&nbsp;Breast',
  '200Breast' => '200&nbsp;Breast',
  '25Fly' => '25&nbsp;Fly',
  '50Fly' => '50&nbsp;Fly',
  '100Fly' => '100&nbsp;Fly',
  '200Fly' => '200&nbsp;Fly',
  '100IM' => '100&nbsp;IM',
  '150IM' => '150&nbsp;IM',
  '200IM' => '200&nbsp;IM',
  '400IM' => '400&nbsp;IM'
];

$pagetitle = "Team Manager View for " . htmlspecialchars($data->gala->name);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas/" . $id) ?>">This Gala</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas/" . $id . "/team-manager") ?>">TM Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Entries</li>
      </ol>
    </nav>

    <h1>Entries for <?= htmlspecialchars($data->gala->name) ?></h1>
    <p class="lead mb-0">
      At <?= htmlspecialchars($data->gala->venue) ?>
    </p>
  </div>
</div>

<div class="container-xl">
  <div class="row">

    <div class="col order-lg-1">
      <div class="cell">
        <h2>Download entries</h2>
        <p class="lead">Download entries for this squad</p>
        <div class="d-grid gap-2">
          <a href="<?= autoUrl("galas/" . $id . "/team-manager-view.csv") ?>" target="_blank" class="btn btn-primary">
            CSV (for Microsoft Excel)
          </a>
          <a href="<?= autoUrl("galas/" . $id . "/team-manager-view.json") ?>" target="_blank" class="btn btn-primary">
            JSON
          </a>
          <a href="<?= autoUrl("galas/" . $id . "/team-manager-view.pdf") ?>" target="_blank" class="btn btn-primary disabled" disabled>
            PDF
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-8 order-lg-0" id="entries-list">

      <h2>Entries for this gala</h2>
      <p class="lead">
        This list shows all entries by swimmers.
      </p>

      <?php if (sizeof($data->entries) > 0) { ?>
        <ul class="list-group mb-3">
          <?php foreach ($data->entries as $entry) { ?>
            <?php $hasNoDD = (!isset($entry->mandate->id) || $entry->mandate->id == null) || (getUserOption($entry->user, 'GalaDirectDebitOptOut')); ?>
            <li class="list-group-item" id="entry-<?= htmlspecialchars($entry->id) ?>">
              <h3><?= htmlspecialchars(\SCDS\Formatting\Names::format($entry->forename, $entry->surname)) ?></h3>
              <div class="row">
                <div class="col-sm-5 col-md-4 col-lg-6">
                  <p class="mb-0">
                    <?= htmlspecialchars($entry->forename) ?> was entered in;
                  </p>
                  <ul class="list-unstyled">
                    <?php $count = 0; ?>
                    <?php foreach ($entry->events as $event) { ?>
                      <?php if ($event->selected) {
                        $count++; ?>
                        <li><?= htmlspecialchars($event->name) ?><?php if (isset($event->entry_time) && $event->entry_time != null) { ?> <em><?= htmlspecialchars($event->entry_time) ?></em><?php } ?></li>
                      <?php } ?>
                    <?php } ?>
                </div>
                <div class="col">
                  <div class="d-sm-none mb-3"></div>

                  <p>
                    <strong>Swim England Number:</strong> <?= htmlspecialchars($entry->asa_number) ?><br>
                    <strong>Age today:</strong> <?= htmlspecialchars($entry->age_today) ?><br>
                    <strong>Age on day:</strong> <?= htmlspecialchars($entry->age_on_last_day) ?><br>
                    <strong>Age at end of year:</strong> <?= htmlspecialchars($entry->age_at_end_of_year) ?><br>
                  </p>

                  <p class="mb-0">
                    <?= $count ?> entries
                  </p>
                </div>
              </div>
            </li>
          <?php } ?>
        </ul>

      <?php } else { ?>
        <div class="alert alert-warning">
          <strong>There are no entries for this gala</strong>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
