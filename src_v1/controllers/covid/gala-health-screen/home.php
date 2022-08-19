<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'COVID Return to Competition Screening';

$date = new DateTime('now', new DateTimeZone('Europe/London'));

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
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  ]);
}

$user = app()->user;
$gala = null;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSquadOpts = true;
  $squads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE squads.Tenant = ? ORDER BY SquadFee DESC, SquadName ASC;");
  $squads->execute([
    $tenant->getId(),
  ]);

  $getGalas = $db->prepare("SELECT GalaName, GalaID FROM galas WHERE Tenant = ? AND GalaDate >= ? ORDER BY GalaDate ASC, GalaName ASC");
  $getGalas->execute([
    app()->tenant->getId(),
    $date->format('Y-m-d'),
  ]);
  $gala = $getGalas->fetch(PDO::FETCH_ASSOC);
}

$getMembers = $db->prepare("SELECT MForename, MSurname, members.MemberID, GalaName, galas.GalaID FROM galaEntries INNER JOIN members ON members.MemberID = galaEntries.MemberID INNER JOIN galas ON galas.GalaID = galaEntries.GalaID WHERE UserID = ? AND GalaDate >= ? ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  $date->format('Y-m-d'),
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

$getLatestCompletion = $db->prepare("SELECT `ID`, `DateTime`, `MemberAgreement`, `Guardian`, `Forename`, `Surname` FROM covidGalaHealthScreen LEFT JOIN users ON users.UserID = covidGalaHealthScreen.Guardian WHERE Member = ? AND Gala = ? ORDER BY `DateTime` DESC LIMIT 1");

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item active" aria-current="page">Competition</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-12">
        <h1>
          COVID-19 Return to Competition Health Screening
        </h1>
        <p class="lead mb-0">
          Making sure you're safe to compete
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">

    <div class="col-lg-8">

      <?php if (isset($_SESSION['CovidGalaSuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>We've saved your COVID-19 competition health survey and risk-awareness declaration</strong>
          </p>
        </div>
      <?php unset($_SESSION['CovidGalaSuccess']);
      } ?>

      <h2>
        About competition health screening
      </h2>
      <p class="lead">
        Some gala hosts require that that all clubs carry out a screening survey of all members who are competing at a given gala.
      </p>
      <p>
        The screen is to inform you and make you aware of the risks.
      </p>
      <p>
        Your club or the gala host may refuse access to the competition or training if you do not have an up to date health screen.
      </p>
      <p>
        You must complete a declaration for each competition you attend.
      </p>

      <?php if ($showSquadOpts) { ?>
        <h2 id="members">Your members and entries</h2>
      <?php } ?>
      <?php if ($member) { ?>

        <ul class="list-group mb-3">
          <?php do {
            $getLatestCompletion->execute([
              $member['MemberID'],
              $member['GalaID']
            ]);

            $latest = $getLatestCompletion->fetch(PDO::FETCH_ASSOC);
          ?>
            <li class="list-group-item">
              <div class="row align-items-center">
                <div class="col-sm">
                  <h3 class="h6 mb-1"><strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></strong> <?= htmlspecialchars($member['GalaName']) ?></h3>
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
                  <div class="mb-3 d-sm-none"></div>
                </div>
                <div class="col-auto">
                  <div class="btn-group">
                    <a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening/new-survey?member=' . $member['MemberID'] . '&gala=' . $member['GalaID'])) ?>" class="btn btn-success">View and sign form</a>
                  </div>
                </div>
              </div>
            </li>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>You don't have any upcoming competitions</strong>
          </p>
          <p class="mb-0">
            Please enter a competition to be able to use this service.
          </p>
        </div>
      <?php } ?>

      <p>
        If asked by a member of club staff, you can <a href="<?= htmlspecialchars(autoUrl("covid/competition-health-screening/any")) ?>">complete a Return to Competition form for a competition you haven't entered</a>.
      </p>

      <?php if (false) { ?>
        <h2 id="squads">Other club members</h2>
        <p class="lead">
          View status for members in your squads
        </p>
        <?php if ($squads && $squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
          <div class="list-group mb-3">
            <?php do { ?>
              <a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening/squads/' . $squad['SquadID'])) ?>" class="list-group-item list-group-item-action">
                <?= htmlspecialchars($squad['SquadName']) ?>
              </a>
            <?php } while ($squad = $squads->fetch(PDO::FETCH_ASSOC)); ?>
          </div>
        <?php } ?>
      <?php } ?>

      <?php if ($gala) { ?>
        <h2 id="galas">Galas</h2>
        <p class="lead">
          View status for members attending galas
        </p>
        <div class="list-group mb-3">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening/galas/' . $gala['GalaID'])) ?>" class="list-group-item list-group-item-action">
              <?= htmlspecialchars($gala['GalaName']) ?>
            </a>
          <?php } while ($gala = $getGalas->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
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
