<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'Generate Report - Contact Tracing';

$getLocations = $db->prepare("SELECT `ID`, `Name` FROM `covidLocations` WHERE `Tenant` = ? ORDER BY `Name` ASC");
$getLocations->execute([
  $tenant->getId(),
]);
$location = $getLocations->fetch(PDO::FETCH_ASSOC);

$date = new DateTime('-6 hour', new DateTimeZone('Europe/London'));
$dateLater = new DateTime('now', new DateTimeZone('Europe/London'));
$threeWeeks = new DateTime('-3 weeks', new DateTimeZone('Europe/London'));

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
      <div class="col-lg-8">
        <h1>
          Generate report
        </h1>
        <p class="lead mb-0">
          Export in any format
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <form action="<?= htmlspecialchars(autoUrl('contact-tracing/reports/go')) ?>" method="get" class="needs-validation" novalidate>

        <p>
          Generate a report for a specific time frame at a specific location. Available in HTML, CSV (for Excel, Numbers, Google Sheets etc) or JSON.
        </p>

        <div class="mb-3">
          <label class="form-label" for="location">Location</label>
          <select class="form-select" id="location" name="location" <?php if (!$location) { ?> disabled <?php } ?> required>
            <option selected>Select a location</option>
            <?php if ($location) { ?>
              <?php do { ?>
                <option value="<?= htmlspecialchars($location['ID']) ?>"><?= htmlspecialchars($location['Name']) ?></option>
              <?php } while ($location = $getLocations->fetch(PDO::FETCH_ASSOC)); ?>
            <?php } ?>
          </select>
        </div>

        <div class="mb-3">
          <p class="mb-2">
            From
          </p>
          <div class="row">
            <div class="col">
              <input type="date" class="form-control" name="from-date" id="from-date" min="<?= htmlspecialchars($threeWeeks->format('Y-m-d')) ?>" max="<?= htmlspecialchars($dateLater->format('Y-m-d')) ?>" required placeholder="<?= htmlspecialchars($date->format('Y-m-d')) ?>" value="<?= htmlspecialchars($date->format('Y-m-d')) ?>">
            </div>
            <div class="col">
              <input type="time" class="form-control" name="from-time" id="from-time" required placeholder="<?= htmlspecialchars($date->format('H:i')) ?>" value="<?= htmlspecialchars($date->format('H:i')) ?>">
            </div>
          </div>
        </div>

        <div class="mb-3">
          <p class="mb-2">
            To
          </p>
          <div class="row">
            <div class="col">
              <input type="date" class="form-control" name="to-date" id="to-date" min="<?= htmlspecialchars($threeWeeks->format('Y-m-d')) ?>" max="<?= htmlspecialchars($dateLater->format('Y-m-d')) ?>" required placeholder="<?= htmlspecialchars($dateLater->format('Y-m-d')) ?>" value="<?= htmlspecialchars($dateLater->format('Y-m-d')) ?>">
            </div>
            <div class="col">
              <input type="time" class="form-control" name="to-time" id="to-time" required placeholder="<?= htmlspecialchars($dateLater->format('H:i')) ?>" value="<?= htmlspecialchars($dateLater->format('H:i')) ?>">
            </div>
          </div>
        </div>

        <p class="mb-2">
          Format
        </p>
        <div class="mb-3">
          <div class="form-check">
            <input type="radio" class="form-check-input" value="html" name="format" id="format-html" checked required>
            <label class="form-check-label" for="format-html">Webpage (HTML)</label>
          </div>
          <div class="form-check">
            <input type="radio" class="form-check-input" value="csv" name="format" id="format-csv" required>
            <label class="form-check-label" for="format-csv">CSV (For Excel)</label>
          </div>
          <div class="form-check">
            <input type="radio" class="form-check-input" value="json" name="format" id="format-json" required>
            <label class="form-check-label" for="format-json">JSON</label>
          </div>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Generate report
          </button>
        </p>
      </form>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
