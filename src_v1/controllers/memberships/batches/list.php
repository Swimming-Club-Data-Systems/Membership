<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$user = app()->user;
$db = app()->db;
$tenant = app()->tenant;

$pagination = new \SCDS\Pagination();
$pagination->records_per_page(10);

$getCount = $getSessions = null;

$today = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d');

$getCount = $db->prepare("SELECT COUNT(*) FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.user WHERE NOT Completed AND users.Active AND users.Tenant = ? AND (DueDate IS NULL OR DueDate <= ?) AND membershipBatch.ID NOT IN (SELECT batch ID FROM onboardingSessions WHERE batch IS NOT NULL)");
$getCount->execute([
  $tenant->getId(),
  $today
]);
$getSessions = $db->prepare("SELECT users.Forename firstName, users.Surname lastName, membershipBatch.ID id FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.user WHERE NOT Completed AND users.Active AND users.Tenant = :tenant AND (DueDate IS NULL OR DueDate <= :today) AND membershipBatch.ID NOT IN (SELECT batch ID FROM onboardingSessions WHERE batch IS NOT NULL) LIMIT :offset, :num");
$getSessions->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
$getSessions->bindValue(':offset', $pagination->get_limit_start(), PDO::PARAM_INT);
$getSessions->bindValue(':num', 10, PDO::PARAM_INT);
$getSessions->bindValue(':today', $today, PDO::PARAM_STR);
$getSessions->execute();

$count = $getCount->fetchColumn();
$session = $getSessions->fetch(PDO::FETCH_OBJ);

if ($pagination->get_limit_start() > 1 && $pagination->get_limit_start() >= $count) halt(404);

$pagination->records($count);

$pagetitle = "Membership batches";
include BASE_PATH . "views/header.php";
?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item active" aria-current="page">Batches</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Membership batches
        </h1>
        <p class="lead mb-0">
          <?= htmlspecialchars($pagination->get_page_description()) ?>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <p>
        Membership batches not forming part of an onboarding or renewal session.
      </p>

      <?php if ($session) { ?>

        <div class="list-group">
          <?php do {
          ?>
            <a href="<?= htmlspecialchars(autoUrl('memberships/batches/' . $session->id . '/edit')) ?>" class="list-group-item list-group-item-action">
              <h2><?= htmlspecialchars($session->firstName . ' ' . $session->lastName) ?></h2>

              <div class="btn btn-primary">
                Edit
              </div>
            </a>
          <?php } while ($session = $getSessions->fetch(PDO::FETCH_OBJ)); ?>
        </div>


        <?= $pagination->render(); ?>

      <?php } else { ?>

        <div class="alert alert-warning">
          <strong>No records found</strong>
        </div>

      <?php } ?>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
