<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'COVID Health Screening';

$squads = null;
$showSquadOpts = false;
// Show if this user is a squad rep
$getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
$getRepCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
]);
$rep = $getRepCount->fetchColumn() > 0;
$showSquadOpts = $rep;

if ($rep) {
  $squads = $db->prepare("SELECT SquadName, SquadID FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad WHERE squadReps.User = ? ORDER BY SquadFee DESC, SquadName ASC;");
  $squads->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
  ]);
}

$user = app()->user;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSquadOpts = true;
  $squads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE squads.Tenant = ? ORDER BY SquadFee DESC, SquadName ASC;");
  $squads->execute([
    $tenant->getId(),
  ]);
}

$getMembers = $db->prepare("SELECT MForename, MSurname, MemberID FROM members WHERE UserID = ? ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

$getLatestCompletion = $db->prepare("SELECT `ID`, `DateTime`, `OfficerApproval`, `ApprovedBy`, `Forename`, `Surname`, `Document` FROM covidHealthScreen LEFT JOIN users ON covidHealthScreen.ApprovedBy = users.UserID WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item active" aria-current="page">Screening</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          COVID-19 Health Screening
        </h1>
        <p class="lead mb-0">
          Making sure you're safe to train
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">

    <div class="col-lg-8">

      <?php if (isset($_SESSION['CovidHealthSurveySuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>We've saved your COVID-19 health survey</strong>
          </p>
        </div>
      <?php unset($_SESSION['CovidHealthSurveySuccess']);
      } ?>

      <h2>
        About our health screening
      </h2>
      <p class="lead">
        Swim England are recommending that all clubs carry out a periodic screening survey of all members who are training.
      </p>
      <p>
        The screen is to inform you and make you aware of the risks.
      </p>
      <p>
        Your club may refuse access to training if you do not have an up to date health screen.
      </p>

      <?php if ($showSquadOpts) { ?>
        <h2 id="members">Your members</h2>
      <?php } ?>
      <?php if ($member) { ?>

        <ul class="list-group mb-3">
          <?php do {
            $getLatestCompletion->execute([
              $member['MemberID']
            ]);

            $latest = $getLatestCompletion->fetch(PDO::FETCH_ASSOC);
          ?>
            <li class="list-group-item">
              <div class="row align-items-center">
                <div class="col-sm">
                  <h3 class="h6 mb-1"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?><?php if ($latest && bool($latest['OfficerApproval'])) { ?> <span class="badge bg-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Approved by club</span><?php } ?></h3>
                  <p class="mb-0">
                    <?php if ($latest) {
                      $time = new DateTime($latest['DateTime'], new DateTimeZone('UTC'));
                      $time->setTimezone(new DateTimeZone('Europe/London'));
                    ?>
                      Latest submission <?= htmlspecialchars($time->format('H:i, j F Y')) ?><?php if (!bool($latest['OfficerApproval']) && !$latest['ApprovedBy']) { ?> <span class="text-warning"><i class="fa fa-minus-circle" aria-hidden="true"></i> Awaiting approval</span><?php } else if (!bool($latest['OfficerApproval']) && $latest['ApprovedBy']) { ?> <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> Rejected by <?= htmlspecialchars($latest['Forename'] . ' ' . $latest['Surname']) ?></span><?php } ?>
                    <?php } else { ?>
                      No survey submitted
                    <?php } ?>
                  </p>
                  <div class="mb-3 d-sm-none"></div>
                </div>
                <div class="col-auto">
                  <div class="btn-group">
                    <?php if ($latest) { ?>
                      <a href="<?= htmlspecialchars(autoUrl('covid/health-screening/members/' . $member['MemberID'])) ?>" class="btn  btn-outline-light-d">View all</a>
                    <?php } ?>
                    <a href="<?= htmlspecialchars(autoUrl('covid/health-screening/members/' . $member['MemberID'] . '/new-survey')) ?>" class="btn btn-success">New submission</a>
                  </div>
                </div>
              </div>
            </li>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>You don't have any members on your account</strong>
          </p>
          <p class="mb-0">
            Please add a member to be able to use this service.
          </p>
        </div>
      <?php } ?>

      <?php if ($showSquadOpts) { ?>
        <h2 id="squads">Other club members</h2>
        <p class="lead">
          View status for members in your squads
        </p>
        <?php if ($squads && $squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
          <div class="list-group mb-3">
            <?php do { ?>
              <a href="<?= htmlspecialchars(autoUrl('covid/health-screening/squads/' . $squad['SquadID'])) ?>" class="list-group-item list-group-item-action">
                <?= htmlspecialchars($squad['SquadName']) ?>
              </a>
            <?php } while ($squad = $squads->fetch(PDO::FETCH_ASSOC)); ?>
          </div>
        <?php } ?>
      <?php } ?>

      <p>
        Information about COVID-19 is available <a href="https://www.nhs.uk/conditions/coronavirus-covid-19/" target="_blank">on the NHS website</a>.
      </p>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
