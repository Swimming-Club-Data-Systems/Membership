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

$stages = SCDS\Onboarding\Session::getDefaultRenewalStages();
$stageNames = SCDS\Onboarding\Session::stagesOrder();
$memberStages = SCDS\Onboarding\Member::getDefaultStages();
$memberStageNames = SCDS\Onboarding\Member::stagesOrder();

// Calculate statistics
$getCount = $db->prepare("SELECT COUNT(*) FROM `onboardingSessions` WHERE `renewal` = ? AND `status` = ?");
$getCount->execute([
  $id,
  'complete',
]);
$complete = $getCount->fetchColumn();

$getCount = $db->prepare("SELECT COUNT(*) FROM `onboardingSessions` WHERE `renewal` = ? AND `status` != ?");
$getCount->execute([
  $id,
  'complete',
]);
$notComplete = $getCount->fetchColumn();

$pagetitle = htmlspecialchars($renewal->start->format('j M Y') . " - " . $renewal->end->format('j M Y')) . " - Renewal - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships/renewal")) ?>">Renewal</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($renewal->start->format('d/m/Y')) ?> - <?= htmlspecialchars($renewal->end->format('d/m/Y')) ?></li>
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
          <a href="<?= htmlspecialchars(autoUrl("memberships/renewal/$id/edit")) ?>" class="btn btn-success">Edit</a>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <main>

        <?php // $renewal->generateSessions() 
        ?>

        <?php if ($complete + $notComplete == 0) { ?>
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>Renewal sessions for this renewal period have not been created yet.</strong>
            </p>
            <p class="mb-0">
              They will be created automatically when this renewal period starts.
            </p>
          </div>
        <?php } else { ?>

          <h2>View member renewal status</h2>

          <div class="d-grid gap-2 mb-3">
            <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("memberships/renewal/$id/renewal-member-list")) ?>">Classic Report</a>
          </div>

          <h2>View associated onboarding sessions</h2>

          <div class="d-grid gap-2 mb-3">
            <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?renewal=" . urlencode($id))) ?>">All</a>
            <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?renewal=" . urlencode($id) . "&type=not_ready")) ?>">Not Ready</a>
            <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?renewal=" . urlencode($id) . "&type=pending")) ?>">Pending</a>
            <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?renewal=" . urlencode($id) . "&type=in_progress")) ?>">In Progress</a>
            <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?renewal=" . urlencode($id) . "&type=complete")) ?>">Complete</a>
          </div>

        <?php } ?>

        <div class="alert alert-info">
          <p class="mb-0">
            <strong>Note</strong>
          </p>
          <p class="mb-0">
            Our new onboarding and renewal systems have soft-launched and are working, but we still have a few things to finish off.
          </p>
        </div>

        <p>
          <?= htmlspecialchars($complete) ?> renewals completed of <?= htmlspecialchars($complete + $notComplete) ?> total.
        </p>

      </main>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
