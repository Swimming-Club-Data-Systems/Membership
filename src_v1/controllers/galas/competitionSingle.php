<?php

$db = app()->db;
$tenant = app()->tenant;
$getGala = $db->prepare("SELECT * FROM `galas` WHERE `GalaID` = ? AND Tenant = ?");
$getGala->execute([
  $id,
  $tenant->getId()
]);

$row = $getGala->fetch(PDO::FETCH_ASSOC);
$course = ['LONG', 'SHORT', 'IRREGULAR'];
$courseStrings = ['Long Course', 'Short Course', 'Other Pool Length or Open Water'];

$closingDate = new DateTime($row['ClosingDate'], new DateTimeZone('Europe/London'));

if ($row == null) {
  halt(404);
}

$pagetitle = $row['GalaName'];
$title = $row['GalaName'];

include BASE_PATH . "views/header.php";
include "galaMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("galas")) ?>">Galas</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("galas/" . $id)) ?>">#<?= htmlspecialchars($id) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>

  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="galaname" class="form-label">Gala Name</label>
          <input type="text" class="form-control" id="galaname" name="galaname" placeholder="<?= htmlspecialchars('e.g. ' . app()->tenant->getKey('CLUB_NAME') . ' Open Meet') ?>" value="<?= htmlspecialchars($row['GalaName']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label" for="description" class="form-label">Description (optional)</label>
          <textarea name="description" id="description" class="form-control font-monospace" aria-describedby="descriptionHelp"><?= htmlspecialchars($row['Description']) ?></textarea>
          <small id="descriptionHelp" class="form-text text-muted">
            A description is optional and will only be displayed if you enter something. Markdown is supported here.
          </small>
        </div>

        <div class="mb-3">
          <label class="form-label" for="length" class="form-label">Course Length</label>
          <select class="form-select" name="length" id="length" required>";
            <?php for ($i = 0; $i < sizeof($course); $i++) {
              if ($course[$i] == $row['CourseLength']) { ?>
                <option selected value="<?= $course[$i] ?>">
                  <?= $courseStrings[$i] ?>
                </option>
              <?php } else { ?>
                <option value="<?= $course[$i] ?>">
                  <?= $courseStrings[$i] ?>
                </option>
            <?php }
            } ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" for="venue" class="form-label">Gala Venue</label>
          <input type="text" class="form-control" id="venue" name="venue" value="<?= htmlspecialchars($row['GalaVenue']) ?>" placeholder="<?= htmlspecialchars('e.g. ' . app()->tenant->getKey('CLUB_NAME') . ' Pool') ?>" required>
        </div>
        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="closingDate" class="form-label">Closing date</label>
              <input type="date" class="form-control" id="closingDate" name="closingDate" placeholder="YYYY-MM-DD" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" value="<?= htmlspecialchars($closingDate->format('Y-m-d')) ?>" required>
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="closingTime" class="form-label">Closing time</label>
              <input type="time" class="form-control" id="closingTime" name="closingTime" placeholder="HH:MM" value="<?= htmlspecialchars($closingDate->format('H:i')) ?>" required>
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="lastDate" class="form-label">Last Day of Gala</label>
          <input type="date" class="form-control" id="galaDate" name="galaDate" placeholder="YYYY-MM-DD" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" value="<?= htmlspecialchars($row['GalaDate']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="per-entry-fee" class="form-label">Per gala entry processing fee</label>
          <div class="input-group">
            <span class="input-group-text">&pound;</span>
            <input type="number" min="0" step="0.01" class="form-control" id="per-entry-fee" name="per-entry-fee" aria-describedby="per-entry-fee-help" placeholder="0.00" required value="<?= htmlspecialchars(MoneyHelpers::intToDecimal($row['ProcessingFee'])) ?>">
            <div class="invalid-feedback">You must enter a numeric value for the per gala entry processing fee - it can be £0</div>
          </div>
          <small id="per-entry-fee-help" class="form-text text-muted">This is the amount added per swimmer entered, not per swim entered. If you apply a processing fee for each swim, add the amount to the fee per swim. To comply with the law on credit/debit card surcharges, you must charge this fee for any payment method you support - even cash or bank transfer</small>
        </div>

        <?php if ($row['HyTek'] == 1) { ?>
          <div class="mb-3">
            <label class="form-label" for="HyTek" class="form-label">Require times?</label>
            <div class="form-check mt-2">
              <input type="checkbox" value="1" class="form-check-input" checked id="HyTek" name="HyTek">
              <label class="form-check-label" for="HyTek">Tick if this is a HyTek gala or needs times from parents</label>
            </div>
          </div>
        <?php } else { ?>
          <div class="mb-3">
            <label class="form-label" for="HyTek" class="form-label">Require times?</label>
            <div class="form-check mt-2">
              <input type="checkbox" value="1" class="form-check-input" id="HyTek" name="HyTek">
              <label class="form-check-label" for="HyTek">Tick if this is a HyTek gala or needs times from parents</label>
            </div>
          </div>
        <?php } ?>

        <div class="mb-3">
          <label class="form-label" for="coachDecides" class="form-label">Coach decides entries?</label>
          <div class="form-check mt-2">
            <input type="checkbox" value="1" class="form-check-input" <?php if ($row['CoachEnters']) { ?>checked<?php } ?> id="coachDecides" name="coachDecides">
            <label class="form-check-label" for="coachDecides">Tick if a coach will make entries for this gala</label>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="approvalNeeded" class="form-label">Approval needed?</label>
          <div class="form-check mt-2">
            <input type="checkbox" value="1" class="form-check-input" <?php if ($row['RequiresApproval']) { ?>checked<?php } ?> id="approvalNeeded" name="approvalNeeded">
            <label class="form-check-label" for="approvalNeeded">Tick if entries must first be approved by a squad rep. Entries are automatically approved if a squad does not have a squad rep.</label>
          </div>
        </div>

        <p>
          <button class="btn btn-success" type="submit" id="submit">
            Save Changes
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
