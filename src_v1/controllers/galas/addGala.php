<?php

$tenant = app()->tenant;

$pagetitle = "Add a gala";

$today = new DateTime('now', new DateTimeZone('Europe/London'));
$today->setTime(23, 59, 59);

include BASE_PATH . "views/header.php";
include "galaMenu.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
      <li class="breadcrumb-item active" aria-current="page">Add gala</li>
    </ol>
  </nav>
  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
    echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
  } ?>
  <h1>Add a gala</h1>
  <p class="lead">Add a gala for members to enter</p>
  <form method="post" action="<?= htmlspecialchars(autoUrl("galas/addgala")) ?>" class="needs-validation" novalidate>
    <div class="row">
      <div class="col-lg-8">
        <div class="mb-3">
          <label for="galaname" class="form-label">Gala Name</label>
          <input type="text" class="form-control" id="galaname" name="galaname" placeholder="eg Chester-le-Street Open" required>
          <div class="invalid-feedback">You must enter a name for the gala</div>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description (optional)</label>
          <textarea class="form-control font-monospace" id="description" name="description" aria-describedby="descriptionHelp"></textarea>
          <small id="descriptionHelp" class="form-text text-muted">
            A description is optional and will only be displayed if you enter something. Markdown is supported here.
          </small>
        </div>
        <div class="mb-3">
          <label for="length" class="form-label">Course Length</label>
          <select class="form-select" name="length" id="length" required>
            <option value="LONG">Long Course</option>
            <option value="SHORT">Short Course</option>
            <option value="IRREGULAR">Other Pool Length or Open Water</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="venue" class="form-label">Gala Venue</label>
          <input type="text" class="form-control" id="venue" name="venue" placeholder="eg Chester-le-Street" required>
          <div class="invalid-feedback">You must enter a venue name for the gala</div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label for="closingDate" class="form-label">Closing date</label>
              <input type="date" class="form-control" id="closingDate" name="closingDate" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($today->format('Y-m-d')) ?>" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" required>
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label for="closingTime" class="form-label">Closing time</label>
              <input type="time" class="form-control" id="closingTime" name="closingTime" placeholder="HH:MM" value="<?= htmlspecialchars($today->format('H:i')) ?>" required>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg">
        <div class="border-start border-info border-5 p-2 mb-3">
          <p class="mb-0">
            <strong><i class="fa fa-info-circle" aria-hidden="true"></i> About closing dates</strong>
          </p>
          <p class="mb-0">
            23:59 represents the end of the day. A closing time of 00:00 would mean the gala closes immediately at the start of the closing date.
          </p>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="mb-3">
          <label for="lastDate" class="form-label">Last day of gala</label>
          <input type="date" class="form-control" id="lastDate" name="lastDate" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($today->format('Y-m-d')) ?>" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" required>
        </div>
        <div class="mb-3">
          <label for="galaFee" class="form-label">Default per swim fee</label>
          <div class="input-group">
            <span class="input-group-text">&pound;</span>
            <input type="num" min="0" step="0.01" class="form-control" id="galaFee" name="galaFee" aria-describedby="galaFeeHelp" placeholder="eg 5.00" required>
            <div class="invalid-feedback">You must enter a numeric value for the default entry fee</div>
          </div>
          <small id="galaFeeHelp" class="form-text text-muted">Enter the <strong>most common</strong> price for swims at this gala. You can adjust individual events next.</small>
        </div>

        <div class="mb-3">
          <label for="per-entry-fee" class="form-label">Per gala entry processing fee</label>
          <div class="input-group">
            <span class="input-group-text">&pound;</span>
            <input type="number" min="0" step="0.01" class="form-control" id="per-entry-fee" name="per-entry-fee" aria-describedby="per-entry-fee-help" placeholder="0.00" required value="<?= htmlspecialchars(MoneyHelpers::intToDecimal($tenant->getKey('DEFAULT_GALA_PROCESSING_FEE'))) ?>">
            <div class="invalid-feedback">You must enter a numeric value for the per gala entry processing fee - it can be £0</div>
          </div>
          <small id="per-entry-fee-help" class="form-text text-muted">This is the amount added per swimmer entered, not per swim entered. If you apply a processing fee for each swim, add the amount to the fee per swim. To comply with the law on credit/debit card surcharges, you must charge this fee for any payment method you support - even cash or bank transfer</small>
        </div>

        <div class="mb-3">
          <label for="HyTek" class="form-label">Require times?</label>
          <div class="form-check">
            <input type="checkbox" value="1" class="form-check-input" id="HyTek" name="HyTek">
            <label class="form-check-label" for="HyTek">Tick if this is a HyTek gala or needs times to be supplied manually</label>
          </div>
        </div>
        <div class="mb-3">
          <label for="coachDecides" class="form-label">Coach decides entries?</label>
          <div class="form-check">
            <input type="checkbox" value="1" class="form-check-input" id="coachDecides" name="coachDecides">
            <label class="form-check-label" for="coachDecides">Tick if a coach will make entries for this gala</label>
          </div>
        </div>
        <div class="mb-3">
          <label for="approvalNeeded" class="form-label">Approval needed?</label>
          <div class="form-check">
            <input type="checkbox" value="1" class="form-check-input" id="approvalNeeded" name="approvalNeeded">
            <label class="form-check-label" for="approvalNeeded">Tick if entries must first be approved by a squad rep (Entries are automatically approved if a squad does not have a squad rep)</label>
          </div>
        </div>
        <p>
          <button class="btn btn-success" type="submit" id="submit">Add gala</button>
        </p>
      </div>
    </div>
  </form>
  <p>This gala will immediately be available for parents to enter, unless coaches decide entries.</p>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
