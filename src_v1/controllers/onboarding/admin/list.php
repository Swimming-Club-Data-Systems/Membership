<?php

$user = app()->user;
$db = app()->db;
$tenant = app()->tenant;

$pagination = new \SCDS\Pagination();
$pagination->records_per_page(10);

$queryString = '';

$getCount = $getSessions = null;
if (isset($_GET['renewal'])) {

  $queryString = 'renewal=' . urlencode($_GET['renewal']) . '&';

  if (isset($_GET['type'])) {
    $getCount = $db->prepare("SELECT COUNT(*) FROM onboardingSessions INNER JOIN users ON users.UserID = onboardingSessions.user WHERE users.Active AND users.Tenant = ? AND onboardingSessions.status = ? AND onboardingSessions.renewal = ?");
    $getCount->execute([
      $tenant->getId(),
      $_GET['type'],
      $_GET['renewal'],
    ]);
    $getSessions = $db->prepare("SELECT users.Forename firstName, users.Surname lastName, onboardingSessions.id FROM onboardingSessions INNER JOIN users ON users.UserID = onboardingSessions.user WHERE users.Active AND users.Tenant = :tenant AND onboardingSessions.status = :statusString AND onboardingSessions.renewal = :renewal ORDER BY created DESC LIMIT :offset, :num");
    $getSessions->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
    $getSessions->bindValue(':statusString', $_GET['type'], PDO::PARAM_STR);
    $getSessions->bindValue(':renewal', $_GET['renewal'], PDO::PARAM_STR);
    $getSessions->bindValue(':offset', $pagination->get_limit_start(), PDO::PARAM_INT);
    $getSessions->bindValue(':num', 10, PDO::PARAM_INT);
    $getSessions->execute();
  } else {
    $getCount = $db->prepare("SELECT COUNT(*) FROM onboardingSessions INNER JOIN users ON users.UserID = onboardingSessions.user WHERE users.Active AND users.Tenant = ? AND onboardingSessions.renewal = ?");
    $getCount->execute([
      $tenant->getId(),
      $_GET['renewal']
    ]);
    $getSessions = $db->prepare("SELECT users.Forename firstName, users.Surname lastName, onboardingSessions.id FROM onboardingSessions INNER JOIN users ON users.UserID = onboardingSessions.user WHERE users.Active AND users.Tenant = :tenant AND onboardingSessions.renewal = :renewal ORDER BY created DESC LIMIT :offset, :num");
    $getSessions->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
    $getSessions->bindValue(':offset', $pagination->get_limit_start(), PDO::PARAM_INT);
    $getSessions->bindValue(':renewal', $_GET['renewal'], PDO::PARAM_STR);
    $getSessions->bindValue(':num', 10, PDO::PARAM_INT);
    $getSessions->execute();
  }
} else {
  if (isset($_GET['type'])) {
    $getCount = $db->prepare("SELECT COUNT(*) FROM onboardingSessions INNER JOIN users ON users.UserID = onboardingSessions.user WHERE users.Active AND users.Tenant = ? AND onboardingSessions.status = ? AND onboardingSessions.renewal IS NULL");
    $getCount->execute([
      $tenant->getId(),
      $_GET['type']
    ]);
    $getSessions = $db->prepare("SELECT users.Forename firstName, users.Surname lastName, onboardingSessions.id FROM onboardingSessions INNER JOIN users ON users.UserID = onboardingSessions.user WHERE users.Active AND users.Tenant = :tenant AND onboardingSessions.status = :statusString ORDER BY created DESC LIMIT :offset, :num");
    $getSessions->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
    $getSessions->bindValue(':statusString', $_GET['type'], PDO::PARAM_STR);
    $getSessions->bindValue(':offset', $pagination->get_limit_start(), PDO::PARAM_INT);
    $getSessions->bindValue(':num', 10, PDO::PARAM_INT);
    $getSessions->execute();
  } else {
    $getCount = $db->prepare("SELECT COUNT(*) FROM onboardingSessions INNER JOIN users ON users.UserID = onboardingSessions.user WHERE users.Active AND users.Tenant = ? AND onboardingSessions.renewal IS NULL");
    $getCount->execute([
      $tenant->getId(),
    ]);
    $getSessions = $db->prepare("SELECT users.Forename firstName, users.Surname lastName, onboardingSessions.id FROM onboardingSessions INNER JOIN users ON users.UserID = onboardingSessions.user WHERE users.Active AND users.Tenant = :tenant ORDER BY created DESC LIMIT :offset, :num");
    $getSessions->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
    $getSessions->bindValue(':offset', $pagination->get_limit_start(), PDO::PARAM_INT);
    $getSessions->bindValue(':num', 10, PDO::PARAM_INT);
    $getSessions->execute();
  }
}

$count = $getCount->fetchColumn();
$session = $getSessions->fetch(PDO::FETCH_OBJ);

if ($pagination->get_limit_start() > 1 && $pagination->get_limit_start() >= $count) halt(404);

$pagination->records($count);

$pagetitle = "Sessions - Onboarding";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('onboarding')) ?>">Onboarding</a></li>
        <li class="breadcrumb-item active" aria-current="page">All</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Onboarding sessions
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

      <?php if ($session) { ?>

        <div class="list-group">
          <?php do {
            $onboardingSession = \SCDS\Onboarding\Session::retrieve($session->id);
          ?>
            <a href="<?= htmlspecialchars(autoUrl('onboarding/sessions/a/' . $session->id)) ?>" class="list-group-item list-group-item-action">
              <h2><?= htmlspecialchars(\SCDS\Formatting\Names::format($session->firstName, $session->lastName)) ?></h2>

              <?php if ($onboardingSession->renewal) { ?>
                <p>
                  Renewal Session
                </p>
              <?php } else { ?>
                <p>
                  Onboarding Session
                </p>
              <?php } ?>

              <dl class="row">
                <dt class="col-md-3">
                  Start
                </dt>
                <dd class="col-9">
                  <?= htmlspecialchars($onboardingSession->start->format('j F Y')) ?>
                </dd>

                <dt class="col-md-3">
                  Due by
                </dt>
                <dd class="col-9">
                  <?= htmlspecialchars($onboardingSession->dueDate->format('j F Y')) ?>
                </dd>

                <dt class="col-md-3">
                  Status
                </dt>
                <dd class="col-9">
                  <?= htmlspecialchars(\SCDS\Onboarding\Session::getStates()[$onboardingSession->status]) ?>
                </dd>

                <?php if ($onboardingSession->status != 'complete' && isset(\SCDS\Onboarding\Session::stagesOrder()[$onboardingSession->getCurrentTask()])) { ?>
                  <dt class="col-md-3">
                    Next stage
                  </dt>
                  <dd class="col-9">
                    <?= htmlspecialchars(\SCDS\Onboarding\Session::stagesOrder()[$onboardingSession->getCurrentTask()]) ?>
                  </dd>
                <?php } ?>

                <?php if ($onboardingSession->status == 'complete') { ?>
                  <dt class="col-md-3">
                    Completed at
                  </dt>
                  <dd class="col-9">
                    <?= htmlspecialchars($onboardingSession->completedAt->format('j F Y')) ?>
                  </dd>
                <?php } ?>
              </dl>

              <div class="btn btn-primary">
                View more
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

    <div class="col">
      <div class="card card-body">
        <h3 class="card-title">
          Filter by state
        </h3>
        <div class="d-grid gap-2">
          <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?" . $queryString)) ?>">All</a>
          <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?" . $queryString . "type=not_ready")) ?>">Not Ready</a>
          <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?" . $queryString . "type=pending")) ?>">Pending</a>
          <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?" . $queryString . "type=in_progress")) ?>">In Progress</a>
          <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl("onboarding/all?" . $queryString . "type=complete")) ?>">Complete</a>
        </div>
      </div>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
