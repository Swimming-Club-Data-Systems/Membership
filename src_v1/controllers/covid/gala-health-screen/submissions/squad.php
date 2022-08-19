<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'COVID Health Screening';

$squad = null;

$user = app()->user;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSquadOpts = true;
  $getSquad = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE squads.Tenant = ? AND squads.SquadID = ?;");
  $getSquad->execute([
    $tenant->getId(),
    $id,
  ]);
  $squad = $getSquad->fetch(PDO::FETCH_ASSOC);
} else {
  $getSquad = $db->prepare("SELECT SquadName, SquadID FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad WHERE squadReps.User = ? AND squadReps.Squad = ?;");
  $getSquad->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    $id,
  ]);
  $squad = $getSquad->fetch(PDO::FETCH_ASSOC);
}

if (!$squad) {
  halt(404);
}

$getMembers = $db->prepare("SELECT MForename, MSurname, MemberID FROM members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID WHERE squadMembers.Squad = ? ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $id,
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

$getLatestCompletion = $db->prepare("SELECT `ID`, `DateTime`, `MemberAgreement`, `Guardian`, `Forename`, `Surname` FROM covidGalaHealthScreen LEFT JOIN users ON users.UserID = covidGalaHealthScreen.Guardian WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

$pagetitle = htmlspecialchars($squad['SquadName']) . ' - COVID Return to Competition Screening';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening')) ?>">Competition</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening#squads')) ?>">Squads</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($squad['SquadName']) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-12">
        <h1>
          <?= htmlspecialchars($squad['SquadName']) ?> COVID-19 Return to Competition Health Screening
        </h1>
        <p class="lead mb-0">
          Keeping everyone safe
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">

    <div class="col-lg-8">

      <?php if ($member) { ?>

        <ul class="list-group mb-3" id="table-area">
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
                      <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> No competition declaration submitted</span>
                    <?php } ?>
                  </p>
                  <?php if (false && !$latest) { ?>
                    <div class="mb-3 d-sm-none"></div>
                  <?php } ?>
                </div>
                <?php if ($latest && bool($latest['MemberAgreement'])) { ?>
                  <div class="col-auto">
                    <div class="mt-3 d-sm-none"></div>
                    <button class="btn btn-warning" data-member-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?>" data-form-submission-id="<?= htmlspecialchars($latest['ID']) ?>" data-action="void" title="Require that <?= htmlspecialchars($member['MForename']) ?> submits a new declaration form">
                      Void form
                    </button>
                  </div>
                <?php } ?>
              </div>
            </li>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>There are no members in <?= htmlspecialchars($squad['SquadName']) ?></strong>
          </p>
        </div>
      <?php } ?>

      <p>
        Information about COVID-19 is available <a href="https://www.nhs.uk/conditions/coronavirus-covid-19/" target="_blank">on the NHS website</a>.
      </p>

      <hr>

      <p>
        Need to quickly void all competition declarations for this squad? Press <strong>Void all</strong> to require all members to submit a new COVID-19 Return to Competition Declaration.
      </p>

      <p>
        <button id="voidAllButton" class="btn btn-warning" data-squad-name="<?= htmlspecialchars($squad['SquadName']) ?>" data-squad-id="<?= htmlspecialchars($id) ?>">
          Void all
        </button>
      </p>
    </div>

  </div>
</div>

<div class="modal" id="revokeModal" tabindex="-1" aria-labelledby="revokeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="revokeModalLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body" id="revokeModalBody">
      </div>
      <div class="modal-footer" id="revokeModalFooter">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="void-button" data-action="void">Void Form</button>
      </div>
    </div>
  </div>
</div>

<div id="js-opts" data-void-ajax-url="<?= htmlspecialchars(autoUrl('covid/risk-awareness/void')) ?>"></div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/covid-risk-awareness/squad-page.js?v=2');
$footer->render();
