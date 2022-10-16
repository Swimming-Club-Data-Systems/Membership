<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

// Get membership years
$today = new DateTime('now', new DateTimeZone('Europe/London'));

$getYears = $db->prepare("SELECT `ID` `id`, `Name` `name`, `StartDate` `start`, `EndDate` `end` FROM `membershipYear` WHERE `Tenant` = ? AND `EndDate` >= ? ORDER BY `StartDate` ASC, `EndDate` ASC, `Name` ASC");
$getYears->execute([
  $tenant->getId(),
  $today->format('Y-m-d'),
]);
$year = $getYears->fetch(PDO::FETCH_OBJ);

$startDate = new DateTime('first day of January next year', new DateTimeZone('Europe/London'));
$endDate = new DateTime('last day of January next year', new DateTimeZone('Europe/London'));

$stages = SCDS\Onboarding\Session::getDefaultRenewalStages();
$stageNames = SCDS\Onboarding\Session::stagesOrder();
$memberStages = SCDS\Onboarding\Member::getDefaultStages();
$memberStageNames = SCDS\Onboarding\Member::stagesOrder();

$pagetitle = "New Renewal Period - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships/renewal")) ?>">Renewal</a></li>
        <li class="breadcrumb-item active" aria-current="page">New</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          New Renewal Period
        </h1>
        <p class="lead mb-0">
          Create a new renewal period
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <main>
        <form class="needs-validation" novalidate method="post">

          <?php if ($year) { ?>

            <div class="card card-body mb-3">
              <h2 class="card-title">
                About Membership Years
              </h2>

              <p class="lead">
                The membership system assigns a type of membership to a specific member for a specified time period (a membership year).
              </p>

              <p>
                At most clubs, the club and Swim England membership years run concurrently with the same start and end dates (usually 1 Jan - 31 Dec). If this applies to you, select the same membership year from both dropdowns.
              </p>

              <p>
                If you club does not have an annual "Club Membership", select <strong>None</strong> for your "Club Membership" membership year.
              </p>

              <p>
                If your club and Swim England membership years run with different dates, please either;
              </p>
              <ul class="mb-0">
                <li>Select the appropriate membership year for club and Swim England fees. Members will pay for both types at the same time.</li>
                <li>Select a membership year for the appropriate membership type you want to renew and select <strong>None</strong> for the other. You can repeat this the other way around when you want to renew the other type (you may wish to remove some of the required stages for one of the renewals). In this case, members will only renew and pay for the type of membership you chose a membership year for.</li>
              </ul>
            </div>

            <div class="mb-3">
              <label for="year-ngb" class="form-label">Membership year (for Swim England)</label>
              <select class="form-select" id="year-ngb" name="year-ngb" required>
                <option selected disabled>Choose a year</option>
                <option value="NONE">None</option>
                <?php do {
                  $start = new DateTime($year->start, new DateTimeZone('Europe/London'));
                  $end = new DateTime($year->end, new DateTimeZone('Europe/London'));
                ?>
                  <option value="<?= htmlspecialchars($year->id) ?>"><?= htmlspecialchars($year->name) ?> (<?= htmlspecialchars($start->format('j M Y')) ?> - <?= htmlspecialchars($end->format('j M Y')) ?>)</option>
                <?php } while ($year = $getYears->fetch(PDO::FETCH_OBJ)); ?>
              </select>
            </div>

            <?php

            // Fetch again
            $getYears->execute([
              $tenant->getId(),
              $today->format('Y-m-d'),
            ]);
            $year = $getYears->fetch(PDO::FETCH_OBJ);
            ?>

            <div class="mb-3">
              <label for="year-club" class="form-label">Membership year (for Club Membership)</label>
              <select class="form-select" id="year-club" name="year-club" required>
                <option selected disabled>Choose a year</option>
                <option value="NONE">None</option>
                <?php do {
                  $start = new DateTime($year->start, new DateTimeZone('Europe/London'));
                  $end = new DateTime($year->end, new DateTimeZone('Europe/London'));
                ?>
                  <option value="<?= htmlspecialchars($year->id) ?>"><?= htmlspecialchars($year->name) ?> (<?= htmlspecialchars($start->format('j M Y')) ?> - <?= htmlspecialchars($end->format('j M Y')) ?>)</option>
                <?php } while ($year = $getYears->fetch(PDO::FETCH_OBJ)); ?>
              </select>
            </div>

            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <label for="start" class="form-label">Renewal period start date</label>
                  <input type="date" class="form-control" id="start" name="start" placeholder="<?= htmlspecialchars($startDate->format('Y-m-d')) ?>" value="<?= htmlspecialchars($startDate->format('Y-m-d')) ?>" required>
                </div>
              </div>
              <div class="col">
                <div class="mb-3">
                  <label for="end" class="form-label">Renewal period end date</label>
                  <input type="date" class="form-control" id="end" name="end" placeholder="<?= htmlspecialchars($endDate->format('Y-m-d')) ?>" value="<?= htmlspecialchars($endDate->format('Y-m-d')) ?>" required>
                </div>
              </div>
            </div>

            <p>
              The renewal period start and end dates should define the period in which people can complete membership renewal.
            </p>

            <p>
              Required stages
            </p>

            <div class="mb-3">
              <?php foreach ($stages as $stage => $details) { ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="<?= htmlspecialchars($stage . '-main-check') ?>" name="<?= htmlspecialchars($stage . '-main-check') ?>" <?php if ($details['required']) { ?>checked<?php } ?> <?php if ($details['required_locked']) { ?>disabled<?php } ?>>
                  <label class="form-check-label" for="<?= htmlspecialchars($stage . '-main-check') ?>">
                    <?= htmlspecialchars($stageNames[$stage]) ?>
                  </label>
                </div>
              <?php } ?>
            </div>

            <p>
              Member information stage includes the following;
            </p>

            <div class="mb-3">
              <?php foreach ($memberStages as $stage => $details) { ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="<?= htmlspecialchars($stage . '-member-check') ?>" name="<?= htmlspecialchars($stage . '-member-check') ?>" <?php if ($details['required']) { ?>checked<?php } ?> <?php if ($details['required_locked']) { ?>disabled<?php } ?>>
                  <label class="form-check-label" for="<?= htmlspecialchars($stage . '-member-check') ?>">
                    <?= htmlspecialchars($memberStageNames[$stage]) ?>
                  </label>
                </div>
              <?php } ?>
            </div>

            <p>
              Photography consents will only be asked from members who are aged under 18 when renewal opens.
            </p>

            <div class="mb-3">
              <p class="mb-2">Payment Options</p>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-card" name="payment-card" value="1" checked>
                <label class="form-check-label" for="payment-card">
                  Credit/Debit card
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-direct-debit" name="payment-direct-debit" value="1" checked>
                <label class="form-check-label" for="payment-direct-debit">
                  Direct Debit
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-cash" name="payment-cash" value="1" disabled>
                <label class="form-check-label" for="payment-cash">
                  Cash
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-cheque" name="payment-cheque" value="1" disabled>
                <label class="form-check-label" for="payment-cheque">
                  Cheque
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="payment-wire" name="payment-wire" value="1" disabled>
                <label class="form-check-label" for="payment-wire">
                  Bank Transfer
                </label>
              </div>
            </div>

            <p>
              Payment types will only be available to users if they also meet the criteria for that type. e.g. to pay by Direct Debit, it must be enabled for their renewal session, your club must have Direct Debit payments enabled and the user must have a Direct Debit mandate set up.
            </p>

            <p>
              You can edit payment options for individual users later - for example if your preferred and only enabled payment method for a renewal is Direct Debit but have a member for whom this is inappropriate, you can enable the card payment option just for them.
            </p>

            <div class="card card-body mb-3">
              <h2>Advanced options</h2>

              <h3>
                Custom Direct Debit Billing Dates
              </h3>

              <p>
                For clubs supporting payment by Direct Debit, you can select a custom date on which to bill the Swim England and Club Membership fee components. Selecting a custom date only applies when members choose to pay renewal fees by Direct Debit - if they pay by card they will pay their entire renewal fee in one go.
              </p>

              <p>
                Members will be charged on their first billing day on or after your selected bill date. Please note that fees will not be automatically added to accounts if users do not complete renewal.
              </p>

              <p>
                To use custom bill dates, you must tick the <em>Use custom bill dates</em> checkbox.
              </p>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="use-custom-bill-dates" name="use-custom-bill-dates">
                <label class="form-check-label" for="use-custom-bill-dates">
                  Use custom bill dates
                </label>
              </div>

              <div class="mb-3">
                <label for="dd-ngb-bills-date" class="form-label">DD Swim England Bill Date</label>
                <input type="date" class="form-control" id="dd-ngb-bills-date" name="dd-ngb-bills-date">
              </div>

              <div class="">
                <label for="dd-club-bills-date" class="form-label">DD Club Membership Bill Date</label>
                <input type="date" class="form-control" id="dd-club-bills-date" name="dd-club-bills-date">
              </div>
            </div>

            <?= \SCDS\CSRF::write(); ?>

            <p>
              <button type="submit" class="btn btn-success">
                Add renewal period
              </button>
            </p>

          <?php } else { ?>

            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>Please create a membership year which ends in future before creating a renewal period</strong>
              </p>
            </div>

          <?php } ?>
        </form>
      </main>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
