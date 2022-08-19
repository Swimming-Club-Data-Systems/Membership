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

$getLatestCompletion = $db->prepare("SELECT `ID`, `DateTime`, `OfficerApproval`, `ApprovedBy`, `Forename`, `Surname`, `Document` FROM covidHealthScreen LEFT JOIN users ON covidHealthScreen.ApprovedBy = users.UserID WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

$pagetitle = htmlspecialchars($squad['SquadName']) . ' - COVID Health Screening';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/health-screening')) ?>">Screening</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/health-screening#squads')) ?>">Squads</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($squad['SquadName']) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($squad['SquadName']) ?> Health Screening
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
              $member['MemberID']
            ]);

            $latest = $getLatestCompletion->fetch(PDO::FETCH_ASSOC);
          ?>
            <li class="list-group-item">
              <div class="row align-items-center">
                <div class="col-sm">
                  <h3 class="h6 mb-1"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></h3>
                  <?php if ($latest && $latest['Document']) { ?>
                    <p class="mb-0 font-weight-bold">
                      <?php if (bool($latest['OfficerApproval'])) { ?> <span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Approved by <?= htmlspecialchars(\SCDS\Formatting\Names::format($latest['Forename'], $latest['Surname'])) ?></span><?php } ?><?php if (!bool($latest['OfficerApproval']) && !$latest['ApprovedBy']) { ?> <span class="text-warning"><i class="fa fa-minus-circle" aria-hidden="true"></i> Awaiting approval</span><?php } else if (!bool($latest['OfficerApproval']) && $latest['ApprovedBy']) { ?> <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> Rejected by <?= htmlspecialchars(\SCDS\Formatting\Names::format($latest['Forename'], $latest['Surname'])) ?></span><?php } ?>
                    </p>
                  <?php } ?>
                  <?php if ($latest && $latest['Document']) {
                    $time = new DateTime($latest['DateTime'], new DateTimeZone('UTC'));
                    $time->setTimezone(new DateTimeZone('Europe/London'));
                  ?>
                    <p class="mb-0">
                      Latest submission <?= htmlspecialchars($time->format('H:i, j F Y')) ?>
                    </p>
                    <?php if (!bool($latest['OfficerApproval'])) { ?>
                      <p class="mt-3 mb-0">
                        <button class="btn btn-primary review-button" type="button" data-review-id="<?= htmlspecialchars($latest['ID']) ?>" data-review-document="<?= htmlspecialchars($latest['Document']) ?>" data-member-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?>">
                          Review<?php if ($latest['ApprovedBy']) { ?> again<?php } ?>
                        </button>
                      </p>
                    <?php } ?>
                  <?php } else if ($latest && !bool($latest['OfficerApproval'])) {
                    $time = new DateTime($latest['DateTime'], new DateTimeZone('UTC'));
                    $time->setTimezone(new DateTimeZone('Europe/London'));
                  ?>
                    <p class="mb-0 text-danger font-weight-bold">
                      <i class="fa fa-times-circle" aria-hidden="true"></i> New Health Survey requested from <?= htmlspecialchars($member['MForename'])?> at <?= htmlspecialchars($time->format('H:i, j F Y')) ?><?php if ($latest['ApprovedBy']) { ?> by <?= htmlspecialchars(\SCDS\Formatting\Names::format($latest['Forename'], $latest['Surname'])) ?></span><?php } ?>
                    </p>
                  <?php } else { ?>
                    <p class="mb-0">
                      No survey submitted
                    </p>
                  <?php } ?>
                </div>
                <div class="col-auto">
                  <div class="mt-3 d-sm-none"></div>
                  <div class="btn-group">
                    <?php if ($latest) { ?>
                      <?php if (bool($latest['OfficerApproval'])) { ?>
                        <button class="btn btn-warning" data-member-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?>" data-form-submission-id="<?= htmlspecialchars($latest['ID']) ?>" data-action="void" title="Require that <?= htmlspecialchars($member['MForename']) ?> submits a new health survey">
                          Void survey
                        </button>
                      <?php } ?>
                      <a href="<?= htmlspecialchars(autoUrl('covid/health-screening/members/' . $member['MemberID'])) ?>" class="btn btn-dark-l btn-outline-light-d">View all</a>
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
            <strong>There are no members in <?= htmlspecialchars($squad['SquadName']) ?></strong>
          </p>
        </div>
      <?php } ?>

      <p>
        Information about COVID-19 is available <a href="https://www.nhs.uk/conditions/coronavirus-covid-19/" target="_blank">on the NHS website</a>.
      </p>

      <hr>

      <p>
        Need to quickly void all surveys for this squad? Press <strong>Void all</strong> to require all members to submit a new COVID-19 Health Survey.
      </p>

      <p>
        <button id="voidAllButton" class="btn btn-warning" data-squad-name="<?= htmlspecialchars($squad['SquadName']) ?>" data-squad-id="<?= htmlspecialchars($id) ?>">
          Void all
        </button>
      </p>
    </div>

  </div>
</div>

<div class="modal " id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reviewModalLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body" id="reviewModalBody">
        ...
      </div>
      <div class="modal-footer" id="reviewModalFooter">
        <div class="d-block w-100">
          <div class="row">
            <div class="col">
              <p class="mb-0">
                <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Close</button>
              </p>
            </div>
            <div class="col-auto">
              <p class="mb-0">
                <button type="button" class="btn btn-danger review-confirm-button" id="reject-button" data-action="reject">Reject</button>
                <button type="button" class="btn btn-success review-confirm-button" id="approve-button" data-action="approve">Approve</button>
              </p>
            </div>
          </div>
        </div>
      </div>
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

<div id="js-opts" data-ajax-url="<?= htmlspecialchars(autoUrl('covid/health-screening/approval')) ?>" data-void-ajax-url="<?= htmlspecialchars(autoUrl('covid/health-screening/void')) ?>"></div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/covid-health-screen/squad-page.js?v=3');
$footer->render();
