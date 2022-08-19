<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'COVID Risk Awareness';
if (mb_strtoupper(app()->tenant->getKey('ASA_CLUB_CODE')) == 'UOSZ') {
  $pagetitle = htmlspecialchars(UOS_RETURN_FORM_NAME);
}

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

$getLatestCompletion = $db->prepare("SELECT `ID`, `DateTime`, `MemberAgreement`, `Guardian`, `Forename`, `Surname` FROM covidRiskAwareness LEFT JOIN users ON users.UserID = covidRiskAwareness.Guardian WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item active" aria-current="page">Risk Awareness</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?php if (mb_strtoupper(app()->tenant->getKey('ASA_CLUB_CODE')) == 'UOSZ') { ?><?= htmlspecialchars(UOS_RETURN_FORM_NAME) ?><?php } else { ?>COVID-19 Risk Awareness Forms<?php } ?>
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

      <?php if (isset($_SESSION['CovidRiskAwarenessSuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>We've saved your COVID-19 Risk Awareness form</strong>
          </p>
        </div>
      <?php unset($_SESSION['CovidRiskAwarenessSuccess']);
      } ?>

      <?php if (mb_strtoupper(app()->tenant->getKey('ASA_CLUB_CODE')) == 'UOSZ') { ?>
        <p>
        All members need to complete a COVID-19 Sport Sheffield Return to Training Form.
        </p>

        <p>
          If you're a member of a Swim England club, you may have filled out a very similar form at home.
        </p>
      <?php } else { ?>
        <p>
          All members need to complete a COVID-19 risk awareness declaration.
        </p>
      <?php } ?>

      <?php if ($showSquadOpts) { ?>
        <h2 id="members">Your members</h2>
      <?php } ?>
      <?php if ($member) { ?>
        <ul class="list-group mb-3">
          <?php do {
            $getLatestCompletion->execute([
              $member['MemberID'],
            ]);
            $latest = $getLatestCompletion->fetch(PDO::FETCH_ASSOC);
          ?>
            <li class="list-group-item">
              <div class="row align-items-center">
                <div class="col-sm">
                  <p class="mb-0"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></p>
                  <p class="mb-0">
                    <?php if ($latest) {
                      $time = new DateTime($latest['DateTime'], new DateTimeZone('UTC'));
                      $time->setTimezone(new DateTimeZone('Europe/London'));
                    ?>
                      <?php if (bool($latest['MemberAgreement'])) { ?>
                        <span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Signed at <?= htmlspecialchars($time->format('H:i, j F Y')) ?><?php if ($latest['Guardian']) { ?> with <?= htmlspecialchars($latest['Forename'] . ' ' . $latest['Surname']) ?> as parent/guardian<?php } ?></span>
                      <?php } else { ?>
                        <span class="text-warning"><i class="fa fa-minus-circle" aria-hidden="true"></i> A new declaration form is required</span>
                      <?php } ?>
                    <?php } else { ?>
                      <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> No risk awareness declaration submitted</span>
                    <?php } ?>
                  </p>
                  <?php if (!$latest) { ?>
                    <div class="mb-3 d-sm-none"></div>
                  <?php } ?>
                </div>
                <?php if (!$latest || ($latest && !bool($latest['MemberAgreement']))) { ?>
                  <div class="col-auto">
                    <a class="btn btn-success" href="<?= htmlspecialchars(autoUrl('covid/risk-awareness/members/' . $member['MemberID'] . '/new-form')) ?>">
                      View and sign form
                    </a>
                  </div>
                <?php } ?>
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
              <a href="<?= htmlspecialchars(autoUrl('covid/risk-awareness/squads/' . $squad['SquadID'])) ?>" class="list-group-item list-group-item-action">
                <?= htmlspecialchars($squad['SquadName']) ?>
              </a>
            <?php } while ($squad = $squads->fetch(PDO::FETCH_ASSOC)); ?>
          </div>
        <?php } ?>
      <?php } ?>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
