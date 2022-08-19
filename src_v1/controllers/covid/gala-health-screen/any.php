<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'COVID Return to Competition Screening';

$date = new DateTime('now', new DateTimeZone('Europe/London'));

$getGalas = $db->prepare("SELECT GalaName, GalaID FROM galas WHERE Tenant = ? AND GalaDate >= ? ORDER BY GalaDate ASC, GalaName ASC");
$getGalas->execute([
  app()->tenant->getId(),
  $date->format('Y-m-d'),
]);
$gala = $getGalas->fetch(PDO::FETCH_ASSOC);

$getMembers = $db->prepare("SELECT MForename, MSurname, members.MemberID FROM members WHERE UserID = ? ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
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
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening')) ?>">Competition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Any Member</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-12">
        <h1>
          COVID-19 Return to Competition Health Screening
        </h1>
        <p class="lead mb-0">
          Complete a form for any of your members for any competition
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">

    <div class="col-lg-8">

      <?php if ($gala && $member) { ?>
        <ol class="list-unstyled">
          <?php do { ?>

            <?php
            $getMembers->execute([
              $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
            ]);
            ?>

            <li>
              <h2><?= htmlspecialchars($gala['GalaName']) ?></h2>

              <p>
                Complete a form for any of your members.
              </p>

              <?php if ($member) { ?>

                <ul class="list-group mb-3">
                  <?php do {
                    $getLatestCompletion->execute([
                      $member['MemberID'],
                      $gala['GalaID']
                    ]);

                    $latest = $getLatestCompletion->fetch(PDO::FETCH_ASSOC);
                  ?>
                    <li class="list-group-item">
                      <div class="row align-items-center">
                        <div class="col-sm">
                          <h3 class="h6 mb-1"><strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></strong></h3>
                          <p class="mb-0">
                            <?php if ($latest) {
                              $time = new DateTime($latest['DateTime'], new DateTimeZone('UTC'));
                              $time->setTimezone(new DateTimeZone('Europe/London'));
                            ?>
                              <?php if (bool($latest['MemberAgreement'])) { ?>
                                <span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Signed at <?= htmlspecialchars($time->format('H:i, j F Y')) ?><?php if ($latest['Guardian']) { ?> with <?= htmlspecialchars(\SCDS\Formatting\Names::format($latest['Forename'], $latest['Surname'])) ?> as parent/guardian<?php } ?></span>
                              <?php } else { ?>
                                <span class="text-warning"><i class="fa fa-minus-circle" aria-hidden="true"></i> A new declaration form is required</span>
                              <?php } ?>
                            <?php } else { ?>
                              <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> No competition declaration submitted</span>
                            <?php } ?>
                          </p>
                          <div class="mb-3 d-sm-none"></div>
                        </div>
                        <div class="col-auto">
                          <div class="btn-group">
                            <a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening/new-survey?member=' . $member['MemberID'] . '&gala=' . $gala['GalaID'])) ?>" class="btn btn-success">View and sign form</a>
                          </div>
                        </div>
                      </div>
                    </li>
                  <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
                </ul>
              <?php } ?>
            </li>
          <?php } while ($gala = $getGalas->fetch(PDO::FETCH_ASSOC)) ?>
        </ol>
      <?php } else { ?>

        <div class="alert alert-info">
          <p class="mb-0">
            <strong>No options are available to you</strong>
          </p>
          <p class="mb-0">
            This is either because there are no upcoming galas or you have no members connected to your account.
          </p>
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
