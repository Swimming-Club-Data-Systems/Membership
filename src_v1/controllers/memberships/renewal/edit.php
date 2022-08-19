<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

// Get membership years
$today = new DateTime('now', new DateTimeZone('Europe/London'));

$renewal = \SCDS\Onboarding\Renewal::retrieve($id);

if (!$renewal) halt(404);

// $startDate = new DateTime($renewal->start, new DateTimeZone('Europe/London'));
// $endDate = new DateTime($renewal->end, new DateTimeZone('Europe/London'));
// $yearStart = new DateTime($renewal->yearStart, new DateTimeZone('Europe/London'));
// $yearEnd = new DateTime($renewal->yearEnd, new DateTimeZone('Europe/London'));

$stages = $renewal->defaultStages;
$stageNames = SCDS\Onboarding\Session::stagesOrder();
$memberStages = $renewal->defaultMemberStages;
$memberStageNames = SCDS\Onboarding\Member::stagesOrder();

$started = $renewal->isCurrent() || $renewal->isPast() || $renewal->isCreated();

$pagetitle = "Edit - " . htmlspecialchars($renewal->start->format('j M Y') . " - " . $renewal->end->format('j M Y')) . " - Renewal - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships/renewal")) ?>">Renewal</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships/renewal/$id")) ?>"><?= htmlspecialchars($renewal->start->format('d/m/Y')) ?> - <?= htmlspecialchars($renewal->end->format('d/m/Y')) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($renewal->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->end->format('j M Y')) ?> Renewal
        </h1>
        <p class="lead mb-0">
          For the <?php if ($renewal->clubYear) { ?> club membership year <?= htmlspecialchars($renewal->clubYear->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->clubYear->end->format('j M Y')) ?><?php } ?><?php if ($renewal->clubYear && $renewal->ngbYear) { ?> and the <?php } ?><?php if ($renewal->ngbYear) { ?> Swim England membership year <?= htmlspecialchars($renewal->ngbYear->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->ngbYear->end->format('j M Y')) ?><?php } ?>
        </p>
      </div>
      <div class="col text-lg-end">
        <p class="mb-0">
          <a href="<?= htmlspecialchars(autoUrl("memberships/renewal/$id")) ?>" class="btn btn-success">Back</a>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <main>

        <form class="needs-validation" method="post" novalidate>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="start" class="form-label">Renewal period start date</label>
                <input type="date" class="form-control" id="start" name="start" placeholder="<?= htmlspecialchars($renewal->start->format('Y-m-d')) ?>" value="<?= htmlspecialchars($renewal->start->format('Y-m-d')) ?>" required>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="end" class="form-label">Renewal period end date</label>
                <input type="date" class="form-control" id="end" name="end" placeholder="<?= htmlspecialchars($renewal->start->format('Y-m-d')) ?>" value="<?= htmlspecialchars($renewal->start->format('Y-m-d')) ?>" required>
              </div>
            </div>
          </div>

          <p>
            The renewal period start and end dates should define the period in which people can complete membership renewal.
          </p>

          <?php if ($started) { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>This renewal period has started, therefore you can not edit the required stages</strong>
              </p>
              <p class="mb-0">
                You can edit the required stages for an individual user by finding their onboarding session.
              </p>
            </div>
          <?php } ?>

          <p>
            Required stages
          </p>

          <div class="mb-3">
            <?php foreach ($stageNames as $stage => $desc) { ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="<?= htmlspecialchars($stage . '-main-check') ?>" name="<?= htmlspecialchars($stage . '-main-check') ?>" <?php if ($stages->$stage->required) { ?>checked<?php } ?> <?php if ($stages->$stage->required_locked || $started) { ?>disabled<?php } ?>>
                <label class="form-check-label" for="<?= htmlspecialchars($stage . '-main-check') ?>">
                  <?= htmlspecialchars($desc) ?>
                </label>
              </div>
            <?php } ?>
          </div>

          <p>
            Member information stage includes the following;
          </p>

          <div class="mb-3">
            <?php foreach ($memberStageNames as $stage => $desc) { ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="<?= htmlspecialchars($stage . '-member-check') ?>" name="<?= htmlspecialchars($stage . '-member-check') ?>" <?php if ($memberStages->$stage->required) { ?>checked<?php } ?> <?php if ($memberStages->$stage->required_locked || $started) { ?>disabled<?php } ?>>
                <label class="form-check-label" for="<?= htmlspecialchars($stage . '-member-check') ?>">
                  <?= htmlspecialchars($desc) ?>
                </label>
              </div>
            <?php } ?>
          </div>

          <p>
            Photography consents will only be asked from members who are aged under 18 when renewal opens.
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

              <p>
                <strong>Changes made here will not apply to any member who has already completed renewal.</strong>
              </p>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="use-custom-bill-dates" name="use-custom-bill-dates" <?php if ($renewal->metadata->custom_direct_debit_bill_dates && ($renewal->metadata->custom_direct_debit_bill_dates->club || $renewal->metadata->custom_direct_debit_bill_dates->ngb)) { ?>checked<?php } ?>>
                <label class="form-check-label" for="use-custom-bill-dates">
                  Use custom bill dates
                </label>
              </div>

              <div class="mb-3">
                <label for="dd-ngb-bills-date" class="form-label">DD Swim England Bill Date</label>
                <input type="date" class="form-control" id="dd-ngb-bills-date" name="dd-ngb-bills-date" value="<?php if ($renewal->metadata->custom_direct_debit_bill_dates && $renewal->metadata->custom_direct_debit_bill_dates->ngb) { ?><?= htmlspecialchars($renewal->metadata->custom_direct_debit_bill_dates->ngb) ?><?php } ?>">
              </div>

              <div class="">
                <label for="dd-club-bills-date" class="form-label">DD Club Membership Bill Date</label>
                <input type="date" class="form-control" id="dd-club-bills-date" name="dd-club-bills-date" value="<?php if ($renewal->metadata->custom_direct_debit_bill_dates && $renewal->metadata->custom_direct_debit_bill_dates->club) { ?><?= htmlspecialchars($renewal->metadata->custom_direct_debit_bill_dates->club) ?><?php } ?>">
              </div>
            </div>

          <?= \SCDS\CSRF::write(); ?>

          <p>
            <button type="submit" class="btn btn-success">
              Save
            </button>
          </p>
        </form>

      </main>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
