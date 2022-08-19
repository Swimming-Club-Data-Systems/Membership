<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

$getLocation = $db->prepare("SELECT `ID`, `Name`, `Address` FROM covidLocations WHERE `ID` = ? AND `Tenant` = ?");
$getLocation->execute([
  $id,
  $tenant->getId()
]);
$location = $getLocation->fetch(PDO::FETCH_ASSOC);

if (!$location) {
  halt(404);
}

if (!app()->user) {
  halt(404);
}

$userOnList = false;
$listsShown = false;

$user = app()->user;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $userSquads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE SquadID = ? AND Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
  $userSquads->execute([
    $_GET['squad'],
    $tenant->getId(),
  ]);
} else {
  $userSquads = $db->prepare("SELECT SquadName, SquadID FROM squadReps INNER JOIN squads ON squadReps.Squad = squads.SquadID WHERE User = ? AND Squad = ? ORDER BY SquadFee DESC, SquadName ASC");
  $userSquads->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    $_GET['squad'],
  ]);
}

$squad = $userSquads->fetch(PDO::FETCH_ASSOC);

if (!$squad) {
  http_response_code(302);
  header("location: " . autoUrl('contact-tracing/check-in/' . $id));
} else {

  // Get Squad Members
  $getMembers = $db->prepare("SELECT MemberID, MForename, MSurname, users.UserID, Forename, Surname, Mobile FROM members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID LEFT JOIN users ON members.UserID = users.UserID WHERE squadMembers.Squad = ? AND members.Tenant = ? ORDER BY MForename ASC, MSurname ASC;");
  $getMembers->execute([
    $_GET['squad'],
    $tenant->getId(),
  ]);

  // Get coaches
  $getCoaches = $db->prepare("SELECT UserID, Forename, Surname, coaches.Type FROM coaches INNER JOIN users ON coaches.User = users.UserID WHERE Squad = ? ORDER BY Forename ASC, Surname ASC");
  $getCoaches->execute([
    $_GET['squad']
  ]);

  // Get reps
  $getReps = $db->prepare("SELECT UserID, Forename, Surname FROM squadReps INNER JOIN users ON squadReps.User = users.UserID WHERE Squad = ? ORDER BY Forename ASC, Surname ASC");
  $getReps->execute([
    $_GET['squad']
  ]);

  $pagetitle = htmlspecialchars($squad['SquadName']) . ' Squad Check In to ' . htmlspecialchars($location['Name']) . ' - Contact Tracing';

  $locationAddress = json_decode($location['Address']);

  // Get member attendance
  $isHere = $db->prepare("SELECT COUNT(*) FROM covidVisitors WHERE `Location` = ? AND `Person` = ? AND `Type` = ? AND `Time` > ? AND NOT `SignedOut`");
  $time = (new DateTime('-1 hour', new DateTimeZone('UTC')))->format("Y-m-d H:i:s");

  $getLatestCovidSurveyCompletion = $db->prepare("SELECT `ID`, `DateTime`, `OfficerApproval`, `ApprovedBy`, `Forename`, `Surname` FROM covidHealthScreen LEFT JOIN users ON covidHealthScreen.ApprovedBy = users.UserID WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

  $getLatestCovidRACompletion = $db->prepare("SELECT `ID`, `DateTime`, `MemberAgreement`, `Guardian`, `Forename`, `Surname` FROM covidRiskAwareness LEFT JOIN users ON users.UserID = covidRiskAwareness.Guardian WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

  $fluidContainer = true;
  include BASE_PATH . 'views/header.php';

?>

  <div class="bg-light mt-n3 py-3 mb-3">
    <div class="container-fluid">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations')) ?>">Locations</a></li>
          <li class="breadcrumb-item active" aria-current="page">Check In</li>
        </ol>
      </nav>

      <div class="row align-items-center">
        <div class="col">
          <h1>
            <?= htmlspecialchars($squad['SquadName']) ?> Check in to <?= htmlspecialchars($location['Name']) ?>
          </h1>
          <p class="lead mb-0">
            <?= htmlspecialchars($locationAddress->streetAndNumber) ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid">

    <div class="row">
      <div class="col-lg-8">

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>An error occurred</strong>
            </p>
            <p class="mb-0">
              <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']['message']) ?>
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']);
        } ?>

        <form method="post" action="<?= htmlspecialchars(autoUrl('contact-tracing/check-in/' . $id . '/squad-register')) ?>">

          <p>
            Tick everyone who is present.
          </p>

          <?php if ($coach = $getCoaches->fetch(PDO::FETCH_ASSOC)) { ?>

            <input type="hidden" name="squad" value="<?= htmlspecialchars($squad['SquadID']) ?>">

            <?= \SCDS\CSRF::write() ?>

            <div class="card mb-3">
              <div class="card-header">
                Coaches
              </div>

              <ul class="list-group list-group-flush">
                <?php do {
                  $isHere->execute([
                    $id,
                    $coach['UserID'],
                    'user',
                    $time,
                  ]);

                  $here = $isHere->fetchColumn() > 0;

                  if (!$here) {
                    $listsShown = true;
                  }

                  if ($user->getId() == $coach['UserID']) {
                    $userOnList = true;
                  }
                ?>
                  <li class="list-group-item <?php if ($here) { ?> bg-light <?php } ?>">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="<?= htmlspecialchars('user-' . $coach['UserID']) ?>" name="<?= htmlspecialchars('user-' . $coach['UserID']) ?>" value="1" <?php if ($here) { ?> disabled <?php } ?> <?php if ($here) { ?> checked <?php } ?>>
                      <label class="form-check-label d-block" for="<?= htmlspecialchars('user-' . $coach['UserID']) ?>"><?= htmlspecialchars($coach['Forename'] . ' ' . $coach['Surname']) ?></label>
                    </div>
                  </li>
                <?php } while ($coach = $getCoaches->fetch(PDO::FETCH_ASSOC)); ?>
              </ul>
            </div>

          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>There are no coaches for this squad</strong>
              </p>
              <p class="mb-0">
                Please check with a member of club staff
              </p>
            </div>
          <?php } ?>

          <?php if ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) { ?>

            <div class="card mb-3">
              <div class="card-header">
                Members
              </div>

              <ul class="list-group list-group-flush">
                <?php do {
                  $isHere->execute([
                    $id,
                    $member['MemberID'],
                    'member',
                    $time,
                  ]);

                  $here = $isHere->fetchColumn() > 0;

                  if (!$here) {
                    $listsShown = true;
                  }

                  $getLatestCovidSurveyCompletion->execute([
                    $member['MemberID'],
                  ]);
                  $cvLatest = $getLatestCovidSurveyCompletion->fetch(PDO::FETCH_ASSOC);

                  $getLatestCovidRACompletion->execute([
                    $member['MemberID'],
                  ]);
                  $cvRALatest = $getLatestCovidRACompletion->fetch(PDO::FETCH_ASSOC);
                ?>
                  <li class="list-group-item <?php if (!$member['UserID'] || $here) { ?> bg-light <?php } ?>">
                    <div class="row">
                      <div class="col">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="<?= htmlspecialchars('member-' . $member['MemberID']) ?>" name="<?= htmlspecialchars('member-' . $member['MemberID']) ?>" value="1" <?php if (!$member['UserID'] || $here) { ?> disabled <?php } ?> <?php if ($here) { ?> checked <?php } ?>>
                          <label class="form-check-label d-block" for="<?= htmlspecialchars('member-' . $member['MemberID']) ?>"><?= htmlspecialchars($member['MForename'] . ' ' . $member['MSurname']) ?> <em class="small"><?php if ($member['UserID']) { ?><?= htmlspecialchars($member['Forename'] . ' ' . $member['Surname']) ?>'s details<?php } else { ?>No details on file<?php } ?></em></label>
                        </div>
                      </div>
                      <div class="col-auto">
                        <?php if ($cvRALatest && bool($cvRALatest['MemberAgreement'])) { ?>
                          <span class="badge badge-sm bg-success">
                            RA <i class="fa fa-check-circle" aria-hidden="true"></i> <span class="visually-hidden">Valid declaration</span>
                          </span>
                        <?php } else { ?>
                          <span class="badge badge-sm bg-danger">
                            RA <i class="fa fa-times-circle" aria-hidden="true"></i> <span class="visually-hidden">form not submitted or new submission required</span>
                          </span>
                        <?php } ?>
                        <?php if ($cvLatest) { ?>
                          <?php if (bool($cvLatest['OfficerApproval'])) { ?>
                            <span class="badge badge-sm bg-success">
                              HS <i class="fa fa-check-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted and approved</span>
                            </span>
                          <?php } else if (!bool($cvLatest['OfficerApproval']) && $cvLatest['ApprovedBy']) { ?>
                            <span class="badge badge-sm bg-danger">
                              HS <i class="fa fa-times-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted and rejected</span>
                            </span>
                          <?php } else if (!bool($cvLatest['OfficerApproval']) && !$cvLatest['ApprovedBy']) { ?>
                            <span class="badge badge-sm bg-warning">
                              HS <i class="fa fa-minus-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted pending approval</span>
                            </span>
                          <?php } ?>
                        <?php } else { ?>
                          <span class="badge badge-sm bg-danger">
                            NO HS <span class="visually-hidden">Survey submitted</span>
                          </span>
                        <?php } ?>
                      </div>
                    </div>
                  </li>
                <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
              </ul>
            </div>

          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>There are no members in this squad</strong>
              </p>
              <p class="mb-0">
                Please check with a member of club staff
              </p>
            </div>
          <?php } ?>

          <?php if ($rep = $getReps->fetch(PDO::FETCH_ASSOC)) { ?>

            <input type="hidden" name="squad" value="<?= htmlspecialchars($squad['SquadID']) ?>">

            <?= \SCDS\CSRF::write() ?>

            <div class="card mb-3">
              <div class="card-header">
                Squad Reps &amp; Covid Liaisons
              </div>

              <ul class="list-group list-group-flush">
                <?php do {
                  $isHere->execute([
                    $id,
                    $rep['UserID'],
                    'user',
                    $time,
                  ]);

                  $here = $isHere->fetchColumn() > 0;

                  if (!$here) {
                    $listsShown = true;
                  }

                  if ($user->getId() == $rep['UserID']) {
                    $userOnList = true;
                  }
                ?>
                  <li class="list-group-item <?php if ($here) { ?> bg-light <?php } ?>">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="<?= htmlspecialchars('rep-' . $rep['UserID']) ?>" name="<?= htmlspecialchars('rep-' . $rep['UserID']) ?>" value="1" <?php if ($here) { ?> disabled <?php } ?> <?php if ($here) { ?> checked <?php } ?>>
                      <label class="form-check-label d-block" for="<?= htmlspecialchars('rep-' . $rep['UserID']) ?>"><?= htmlspecialchars($rep['Forename'] . ' ' . $rep['Surname']) ?></label>
                    </div>
                  </li>
                <?php } while ($rep = $getReps->fetch(PDO::FETCH_ASSOC)); ?>
              </ul>
            </div>

          <?php } ?>

          <?php if ($listsShown) { ?>
            <?php if ($userOnList) { ?>
              <p>
                Please make sure you tick yourself and sign yourself in!
              </p>
            <?php } else { ?>
              <p>
                As you're not on any of the above lists, please complete the other form to check in!
              </p>
            <?php } ?>

            <p>
              <button type="submit" class="btn btn-success">
                Check In
              </button>
            </p>

          <?php } else { ?>
            <p>
              There is nobody to check in for this squad.
            </p>
          <?php } ?>

        </form>

      </div>

      <div class="col">
        <div class="card position-sticky top-3 mb-3">
          <div class="card-header">
            COVID-19 Badge Key
          </div>
          <div class="card-body">

            <p>
              <strong>Health Survey Symbols</strong>
            </p>

            <dl class="row mb-0">
              <dt class="col-sm-3">
                <span class="badge badge-sm bg-success">
                  HS <i class="fa fa-check-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted and approved</span>
                </span>
              </dt>
              <dd class="col-sm-9">
                COVID health survey submitted and approved by staff
              </dd>
              <dt class="col-sm-3">
                <span class="badge badge-sm bg-danger">
                  HS <i class="fa fa-times-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted and rejected or new survey submission requested</span>
                </span>
              </dt>
              <dd class="col-sm-9">
                COVID health survey submitted and rejected by staff or survey voided and new survey submission requested by staff
              </dd>
              <dt class="col-sm-3">
                <span class="badge badge-sm bg-warning">
                  HS <i class="fa fa-minus-circle" aria-hidden="true"></i><span class="visually-hidden">Survey submitted pending approval</span>
                </span>
              </dt>
              <dd class="col-sm-9">
                COVID health survey submitted, pending approval
              </dd>
              <dt class="col-sm-3">
                <span class="badge badge-sm bg-danger">
                  NO HS <span class="visually-hidden"> submitted</span>
                </span>
              </dt>
              <dd class="col-sm-9 mb-0">
                No COVID health survey has been submitted for this member
              </dd>
            </dl>

            <hr>

            <p>
              <strong>Risk Awareness Declaration Symbols</strong>
            </p>

            <dl class="row mb-0">
              <dt class="col-sm-3">
                <span class="badge badge-sm bg-success">
                  RA <i class="fa fa-check-circle" aria-hidden="true"></i> <span class="visually-hidden">Valid declaration</span>
                </span>
              </dt>
              <dd class="col-sm-9">
                COVID Risk Awareness Declaration is up to date
              </dd>
              <dt class="col-sm-3">
                <span class="badge badge-sm bg-danger">
                  RA <i class="fa fa-times-circle" aria-hidden="true"></i> <span class="visually-hidden">form not submitted or new submission required</span>
                </span>
              </dt>
              <dd class="col-sm-9 mb-0">
                A COVID Risk Awareness Declaration is required or a new Risk Awareness Declaration has been requested
              </dd>
            </dl>

          </div>
        </div>
      </div>
    </div>

  </div>

<?php

  $footer = new \SCDS\Footer();
  $footer->addJs('public/js/NeedsValidation.js');
  $footer->useFluidContainer();
  $footer->render();
}
