<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$getMember = $db->prepare("SELECT MemberID, UserID, MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $id,
  $tenant->getId(),
]);

$member = $getMember->fetch(PDO::FETCH_ASSOC);

if (!$member) {
  halt(404);
}

$getCountRep = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad IN (SELECT Squad FROM squadReps WHERE User = ?)");
$getCountRep->execute([
  $id,
  $user->getId(),
]);
$rep = $getCountRep->fetchColumn() > 0;

if (!$rep && !$user->hasPermission('Admin') && !$user->hasPermission('Coach') && !$user->hasPermission('Galas')) {
  if ($member['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
    halt(404);
  }
}

$start = 0;
$page = 0;
if (isset($_GET['page']) && ((int) $_GET['page']) != 0) {
  $page = (int) $_GET['page'];
  $start = ($page - 1) * 10;
} else {
  $page = 1;
}

$getCount = $db->prepare("SELECT COUNT(*) FROM covidHealthScreen WHERE Member = ?");
$getCount->execute([$id]);
$numForms  = $getCount->fetchColumn();
$numPages = ((int)($numForms / 10)) + 1;

$getForms = $db->prepare("SELECT `ID`, `DateTime`, `OfficerApproval`, `Document`, `ApprovedBy`, `Forename`, `Surname` FROM covidHealthScreen LEFT JOIN users ON covidHealthScreen.ApprovedBy = users.UserID WHERE Member = :member ORDER BY `DateTime` DESC LIMIT :offset, :num");
$getForms->bindValue(':member', $id, PDO::PARAM_INT);
$getForms->bindValue(':offset', $start, PDO::PARAM_INT);
$getForms->bindValue(':num', 10, PDO::PARAM_INT);
$getForms->execute();
$form = $getForms->fetch(PDO::FETCH_ASSOC);

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) . ' - COVID Health Screening';

$markdown = new ParsedownExtra();
$markdown->setSafeMode(true);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/health-screening')) ?>">Screening</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars(mb_substr($member['MForename'], 0, 1) . mb_substr($member['MSurname'], 0, 1)) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          COVID-19 Health Screens <small class="text-muted"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></small>
        </h1>
        <p class="lead mb-0">
          Submitted forms
        </p>
      </div>
      <div class="col">
        <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/se.png')) ?>" class="w-50 ms-auto d-none d-lg-flex" alt="Swim England Logo">
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <?php if ($form) { ?>
        <ul class="list-group">
          <?php do {
            $time = new DateTime($form['DateTime'], new DateTimeZone('UTC'));
            $time->setTimezone(new DateTimeZone('Europe/London'));
          ?>
            <li class="list-group-item" id="<?= htmlspecialchars($form['ID']) ?>">
              <h2>
                Submission at <?= htmlspecialchars($time->format('H:i, j F Y')) ?>
              </h2>

              <?php
              $json = null;
              try {
                $json = json_decode($form['Document'], true);
              } catch (Exception $e) {
                // An error occurred
              }
              ?>

              <p class="lead <?php if (!$json) { ?>mb-0<?php } ?>">
                <?php if (bool($form['OfficerApproval'])) { ?>
                  <span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Approved by <?= htmlspecialchars($form['Forename'] . ' ' . $form['Surname']) ?></span>
                <?php } else if (!bool($form['OfficerApproval']) && $form['ApprovedBy']) { ?>
                  <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> Rejected by <?= htmlspecialchars($form['Forename'] . ' ' . $form['Surname']) ?></span>
                <?php } else if (!bool($form['OfficerApproval'])) { ?>
                  <span class="text-warning"><i class="fa fa-minus-circle" aria-hidden="true"></i> Awaiting approval</span>
                <?php } ?>
              </p>

              <?php if ($json) { ?>
                <dl class="row mb-0">
                  <dt class="col-sm-3">Confirmed infection</dt>
                  <dd class="col-sm-9">
                    <?php if ($json['form']['confirmed-infection']['state']) { ?>
                      Yes
                    <?php } else { ?>
                      No
                    <?php } ?>
                    <?php if (isset($json['form']['confirmed-infection']['notes']) && mb_strlen($json['form']['confirmed-infection']['notes']) > 0) { ?>
                      <div class="card card-body p-2 pb-0"><?= $markdown->text($json['form']['confirmed-infection']['notes']) ?></div>
                    <?php } ?>
                  </dd>

                  <dt class="col-sm-3">Recent exposure</dt>
                  <dd class="col-sm-9">
                    <?php if ($json['form']['exposure']['state']) { ?>
                      Yes
                    <?php } else { ?>
                      No
                    <?php } ?>
                    <?php if (isset($json['form']['exposure']['notes']) && mb_strlen($json['form']['exposure']['notes']) > 0) { ?>
                      <div class="card card-body p-2 pb-0"><?= $markdown->text($json['form']['exposure']['notes']) ?></div>
                    <?php } ?>
                  </dd>

                  <dt class="col-sm-3">Underlying medical conditions</dt>
                  <dd class="col-sm-9">
                    <?php if ($json['form']['underlying-medical']['state']) { ?>
                      Yes
                    <?php } else { ?>
                      No
                    <?php } ?>
                    <?php if (isset($json['form']['underlying-medical']['notes']) && mb_strlen($json['form']['underlying-medical']['notes']) > 0) { ?>
                      <div class="card card-body p-2 pb-0"><?= $markdown->text($json['form']['underlying-medical']['notes']) ?></div>
                    <?php } ?>
                  </dd>

                  <dt class="col-sm-3">Lives with shielder</dt>
                  <dd class="col-sm-9">
                    <?php if ($json['form']['live-with-shielder']['state']) { ?>
                      Yes
                    <?php } else { ?>
                      No
                    <?php } ?>
                    <?php if (isset($json['form']['live-with-shielder']['notes']) && mb_strlen($json['form']['live-with-shielder']['notes']) > 0) { ?>
                      <div class="card card-body p-2 pb-0"><?= $markdown->text($json['form']['live-with-shielder']['notes']) ?></div>
                    <?php } ?>
                  </dd>

                  <dt class="col-sm-3">Understands return to training protocols</dt>
                  <dd class="col-sm-9">
                    <?php if ($json['form']['understand-return']['state']) { ?>
                      Yes
                    <?php } else { ?>
                      No
                    <?php } ?>
                    <?php if (isset($json['form']['understand-return']['notes']) && mb_strlen($json['form']['understand-return']['notes']) > 0) { ?>
                      <div class="card card-body p-2 pb-0"><?= $markdown->text($json['form']['understand-return']['notes']) ?></div>
                    <?php } ?>
                  </dd>

                  <dt class="col-sm-3">Able to train</dt>
                  <dd class="col-sm-9">
                    <?php if ($json['form']['able-to-train']['state']) { ?>
                      Yes
                    <?php } else { ?>
                      No
                    <?php } ?>
                  </dd>

                  <dt class="col-sm-3">Sought advice</dt>
                  <dd class="col-sm-9">
                    <?php if ($json['form']['sought-advice']['state']) { ?>
                      Yes
                    <?php } else { ?>
                      No
                    <?php } ?>
                  </dd>

                  <dt class="col-sm-3">Advice received</dt>
                  <dd class="col-sm-9">
                    <?php if ($json['form']['advice-received']['state']) { ?>
                      Yes
                    <?php } else { ?>
                      No
                    <?php } ?>
                    <?php if (isset($json['form']['advice-received']['notes']) && mb_strlen($json['form']['advice-received']['notes']) > 0) { ?>
                      <div class="card card-body p-2 pb-0"><?= $markdown->text($json['form']['advice-received']['notes']) ?></div>
                    <?php } ?>
                  </dd>
                </dl>
              <?php } ?>
            </li>
          <?php } while ($form = $getForms->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
          <ul class="pagination mb-3">
            <?php if ($numForms <= 10) { ?>
              <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
            <?php } else if ($numForms <= 20) { ?>
              <?php if ($page == 1) { ?>
                <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page + 1 ?>">Next</a></li>
              <?php } else { ?>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page - 1 ?>">Previous</a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page - 1 ?>"><?php echo $page - 1 ?></a></li>
                <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
              <?php } ?>
            <?php } else { ?>
              <?php if ($page == 1) { ?>
                <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page + 2 ?>"><?php echo $page + 2 ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page + 1 ?>">Next</a></li>
              <?php } else { ?>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page - 1 ?>">Previous</a></li>
                <?php if ($page > 2) { ?>
                  <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page - 2 ?>"><?php echo $page - 2 ?></a></li>
                <?php } ?>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page - 1 ?>"><?php echo $page - 1 ?></a></li>
                <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
                <?php if ($numForms > $page * 10) { ?>
                  <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
                  <?php if ($numForms > $page * 10 + 10) { ?>
                    <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page + 2 ?>"><?php echo $page + 2 ?></a></li>
                  <?php } ?>
                  <li class="page-item"><a class="page-link" href="<?php echo autoUrl("covid/health-screening/members/" . $member . "?page="); ?><?php echo $page + 1 ?>">Next</a></li>
                <?php } ?>
              <?php } ?>
            <?php } ?>
          </ul>
        </nav>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?> has never submitted a COVID-19 health survey</strong>
          </p>
          <p class="mb-0">
            <a href="<?= htmlspecialchars(autoUrl('covid/health-screening/members/' . $member['MemberID'] . '/new-survey')) ?>" class="alert-link">Start the survey</a>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
