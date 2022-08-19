<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

// Get renewals
$getCount = $db->prepare("SELECT COUNT(*) FROM renewalv2 WHERE Tenant = ?");
$getCount->execute([
  $tenant->getId()
]);
$count = $getCount->fetchColumn();

$pagination = new \SCDS\Pagination();
$pagination->records_per_page(10);
$pagination->records($count);

$getRenewals = $db->prepare("SELECT `id` FROM renewalv2 WHERE Tenant = :tenant ORDER BY `end` DESC, `start` DESC LIMIT :offset, :num");
$getRenewals->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
$getRenewals->bindValue(':offset', $pagination->get_limit_start(), PDO::PARAM_INT);
$getRenewals->bindValue(':num', 10, PDO::PARAM_INT);
$getRenewals->execute();
$renewal = $getRenewals->fetch(PDO::FETCH_OBJ);

$pagetitle = "Membership Renewal - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item active" aria-current="page">Renewal</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Membership Renewal
        </h1>
        <p class="lead mb-3 mb-lg-0">
          Create or view details for a renewal period
        </p>
      </div>
      <div class="col-auto ms-lg-auto">
        <a href="<?= htmlspecialchars(autoUrl('memberships/renewal/new')) ?>" class="btn btn-success">New</a>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <!-- <p>
        The Membership Centre lets clubs track which memberships their members hold in a given year.
      </p> -->

      <?php if ($renewal) { ?>
        <div class="list-group mb-3">
          <?php do { 
            $renewal = \SCDS\Onboarding\Renewal::retrieve($renewal->id);
            ?>
            <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars(autoUrl('memberships/renewal/' . $renewal->id)) ?>" title="<?= htmlspecialchars($renewal->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->end->format('j M Y')) ?> <?= htmlspecialchars($renewal->id) ?>"><?= htmlspecialchars($renewal->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->end->format('j M Y')) ?><br>For the <?php if ($renewal->clubYear) { ?> club membership year <?= htmlspecialchars($renewal->clubYear->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->clubYear->end->format('j M Y')) ?><?php } ?><?php if ($renewal->clubYear && $renewal->ngbYear) { ?><br><?php } ?><?php if ($renewal->ngbYear) { ?> For the Swim England membership year <?= htmlspecialchars($renewal->ngbYear->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->ngbYear->end->format('j M Y')) ?><?php } ?></a>
          <?php } while ($renewal = $getRenewals->fetch(PDO::FETCH_OBJ)); ?>
        </div>

        <?= $pagination->render() ?>
      <?php } else { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>There are no membership renewal periods to display</strong>
          </p>
          <p class="mb-0">
            Create a new membership renewal period to get started.
          </p>
        </div>
      <?php } ?>
    </div>

    <p>
      Are you looking for information from previous renewals? <a href="<?= htmlspecialchars(autoUrl('renewal')) ?>">Visit the legacy renewal system instead</a>.
    </p>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
