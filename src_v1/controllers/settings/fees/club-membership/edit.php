<?php

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

$db = app()->db;
$tenant = app()->tenant;

$getClass = $db->prepare("SELECT `ID`, `Name`, `Description`, `Fees`, `Type` FROM `clubMembershipClasses` WHERE `ID` = ? AND `Tenant` = ?");
$getClass->execute([
  $id,
  $tenant->getId(),
]);
$class = $getClass->fetch(PDO::FETCH_ASSOC);

if (!$class) {
  halt(404);
}

$type = 'Club Membership';
switch ($class['Type']) {
  case 'national_governing_body':
    $type = htmlspecialchars(app()->tenant->getKey('NGB_NAME')) . ' Membership';
    break;
  case 'other':
    $type = 'Other (Arbitrary) Membership';
    break;
}

$json = json_decode($class['Fees']);

$fees = [];
foreach ($json->fees as $value) {
  $fees[] = (string) (\Brick\Math\BigDecimal::of((string) $value))->withPointMovedLeft(2)->toScale(2);
}

$fluidContainer = true;

$pagetitle = "Edit " . htmlspecialchars($class['Name']);

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-fees');
      ?>
    </aside>
    <div class="col-md-9">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings')) ?>">Settings</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings/fees')) ?>">Fees</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees')) ?>">Memberships</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Class</li>
        </ol>
      </nav>

      <main>
        <h1><?= htmlspecialchars($class['Name']) ?></h1>
        <p class="lead">Set amounts for membership fees</p>

        <form method="post" class="needs-validation" novalidate>

          <div class="mb-3">
            <label class="form-label" for="membership-type-name">Membership Type</label>
            <input type="text" name="membership-type-name" id="membership-type-name" class="form-control" value="<?= htmlspecialchars($type) ?>" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label" for="class-name">Class Name</label>
            <input type="text" name="class-name" id="class-name" class="form-control" required value="<?= htmlspecialchars($class['Name']) ?>">
            <div class="invalid-feedback">
              Please provide a name for this type of membership
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="class-description">Description (optional)</label>
            <textarea class="form-control" name="class-description" id="class-description" rows="5"><?= htmlspecialchars($class['Description']) ?></textarea>
          </div>

          <div class="mb-3" id="fee-type">
            <p class="mb-2">Fee type</p>
            <div class="form-check">
              <input type="radio" id="fee-n" name="class-fee-type" class="form-check-input" <?php if ($json->type == 'NSwimmers') { ?>checked<?php } ?> value="NSwimmers" required <?php if ($class['Type'] == 'national_governing_body') { ?>disabled<?php } ?>>
              <label class="form-check-label" for="fee-n">N Members</label>
            </div>
            <div class="form-check">
              <input type="radio" id="fee-person" name="class-fee-type" class="form-check-input" <?php if ($json->type == 'PerPerson') { ?>checked<?php } ?> value="PerPerson">
              <label class="form-check-label" for="fee-person">Per Person</label>
            </div>
          </div>

          <div id="per-person" class="<?php if ($json->type != 'PerPerson') { ?>d-none<?php } ?>">
            <div class="mb-3">
              <label class="form-label" for="class-price">Price</label>
              <input type="number" name="class-price" id="class-price" class="form-control person-fee-input" <?php if (isset($fees[0])) { ?> value="<?= htmlspecialchars($fees[0]) ?>" <?php } ?> min=" 0" step="0.01" placeholder="0" <?php if ($json->type == 'PerPerson') { ?>required<?php } ?>>
              <div class="invalid-feedback">
                Please provide a price for this type of membership
              </div>
            </div>
          </div>

          <div id="n-swimmers" class="<?php if ($json->type != 'NSwimmers' || $class['Type'] == 'national_governing_body') { ?>d-none<?php } ?>">
            <div id="fees-box" data-init="true" data-fees="<?= htmlspecialchars(json_encode($fees)) ?>"></div>

            <p>
              <button class="btn btn-primary" id="add-guest" type="button">
                Add another
              </button>
            </p>
          </div>

          <h2>Month Discounts</h2>

          <p class="lead">
            If your club applies discounts on a depending on when a member joins, set the total here.
          </p>

          <p>
            Please only choose a percentage discount or defined value discount for a given month. You can however mix and match throughout the year. The behaviour of the membership system is undefined if you supply values for both percentage and defined value.
          </p>

          <p>
            For N Members classes, value discounts would be applied on a per person basis. It is recommended to use a percentage discount in this case. For example a £5 discount and two members would mean £10 is taken off the total.
          </p>

          <p>
            Discounts are applied only when fees are calculated. This means if you add a new member in August, but they don't complete payment until September, they would pay the August amount unless you manually amend it.
          </p>

          <?php for ($i = 1; $i < 13; $i++) {
            $date = new DateTime("2020-$i-01", new DateTimeZone('Europe/London')); ?>
            <h3><?= htmlspecialchars($date->format('F')) ?></h3>
            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <label for="<?= htmlspecialchars("value-" . $date->format("m")) ?>" class="form-label"><span class="sr-only"><?= htmlspecialchars($date->format("F")) ?></span> Discount Value</label>
                  <input type="num" class="form-control" id="<?= htmlspecialchars("value-" . $date->format("m")) ?>" name="<?= htmlspecialchars("value-" . $date->format("m")) ?>" <?php if (isset($json->discounts->value[$i-1])) { ?>value="<?= htmlspecialchars(MoneyHelpers::intToDecimal($json->discounts->value[$i-1])) ?>"<?php } ?>>
                </div>
              </div>
              <div class="col">
                <div class="mb-3">
                  <label for="<?= htmlspecialchars("percent-" . $date->format("m")) ?>" class="form-label"><span class="sr-only"><?= htmlspecialchars($date->format("F")) ?></span> Discount Percentage</label>
                  <input type="num" class="form-control" id="<?= htmlspecialchars("percent-" . $date->format("m")) ?>" name="<?= htmlspecialchars("percent-" . $date->format("m")) ?>" <?php if (isset($json->discounts->percent[$i-1])) { ?>value="<?= htmlspecialchars($json->discounts->percent[$i-1]) ?>"<?php } ?>>
                </div>
              </div>
            </div>
          <?php } ?>

          <?= \SCDS\CSRF::write(); ?>

          <p>
            <button type="submit" class="btn btn-success">Save</button>
          </p>

        </form>

      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->addJs('public/js/settings/club-membership-fees.js');
$footer->render();
