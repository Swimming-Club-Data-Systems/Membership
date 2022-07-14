<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

if (!isset($_GET['member']) || !isset($_GET['gala'])) halt(404);

$id = $_GET['member'];

$date = new DateTime('now', new DateTimeZone('Europe/London'));

$getMember = $db->prepare("SELECT MemberID, UserID, MForename, MSurname, DateOfBirth FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $id,
  $tenant->getId(),
]);

$member = $getMember->fetch(PDO::FETCH_ASSOC);

if (!$member) {
  halt(404);
}

$getGala = $db->prepare("SELECT GalaName FROM galas WHERE GalaID = ? AND GalaDate >= ? AND Tenant = ?");
$getGala->execute([
  $_GET['gala'],
  $date->format('Y-m-d'),
  $tenant->getId(),
]);

$gala = $getGala->fetch(PDO::FETCH_ASSOC);

if (!$gala) {
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

$getParent = $db->prepare("SELECT Forename, Surname FROM users WHERE UserID = ?");
$getParent->execute([
  $member['UserID'],
]);

$parent = $getParent->fetch(PDO::FETCH_ASSOC);

$today = new DateTime('now', new DateTimeZone('Europe/London'));
$age = new DateTime($member['DateOfBirth'], new DateTimeZone('Europe/London'));

$age = $age->diff($today);
$age = (int) $age->format('%y');

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) . ' - COVID Return to Competition Screening';

include BASE_PATH . 'views/header.php';

?>

<style>
  .list-with-border {
    list-style-position: inside;
    display: block;
    padding-left: 0;
  }

  .list-with-border>li {
    padding: 1rem;
    background: var(--light);
    margin: 0 0 1rem 0;
    border-radius: .25rem;
  }

  .list-with-border>li::marker {
    text-align: left;
    font-weight: bold;
    padding: 0;
    margin: 0;
    display: block;
    margin: 0 0 0.5rem 0;
  }
</style>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/competition-health-screening')) ?>">Competition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Survey</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Return to Competition <small class="text-muted"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></small>
        </h1>
        <p class="lead mb-0">
          COVID-19 and Risk Awareness Declaration.
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

    <div class="col">

      <?php if (isset($_SESSION['CovidGalaError'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>There was a problem saving your COVID-19 health survey</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['CovidGalaError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['CovidGalaError']);
      } ?>

      <div class="row">
        <div class="col-lg-8">
          <p>
            This form is for <em><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></em> at <em><?= htmlspecialchars($gala['GalaName']) ?></em> only. This declaration will expire seven days after submission. If the gala spans multiple weekends, you must resubmit this form no more than seven days ahead of each weekend.
          </p>

          <form method="post" class="needs-validation" novalidate>

            <input type="hidden" name="member" value="<?= htmlspecialchars($member['MemberID']) ?>">
            <input type="hidden" name="gala" value="<?= htmlspecialchars($_GET['gala']) ?>">

            <p>
              [I / my child] <?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?> [am / is] able to participate in this competition having completed and signed the relevant Health Survey and Return to Training Declaration forms as requested by <?= htmlspecialchars(app()->tenant->getName()) ?>.
            </p>

            <p>
              By signing this declaration, I confirm that [I / my child] [am / is] free from any symptoms related to the COVID-19 virus, I understand the main symptoms include:
            </p>

            <ul>
              <li>a high temperature – this means you feel hot to touch on your chest or back</li>
              <li>a new, continuous cough – this means coughing a lot for more than an hour, or three or more coughing episodes in 24 hours</li>
              <li>a loss or change to your sense of smell or taste.</li>
            </ul>

            <p>
              I am also confirming all in my household remain symptom free, and anyone taking me to or from the competition and attending the competition with me is also symptom free from the virus.
            </p>

            <p>
              [I / My child] return(s) to competition knowing that participation cannot be without risk, I am therefore aware of these risks associated with the COVID-19 virus, but [I still wish / I still wish my child] to participate in the competition.
            </p>

            <p>
              I understand the processes and protocols the <em><?= htmlspecialchars($gala['GalaName']) ?></em> meet organiser have put in place in order to reduce risks and [I / my child] will adhere to these in order to protect [my / my child’s] health and the health of other members, staff and other users of the facility.
            </p>

            <p>
              I also understand that the meet organiser will have to be flexible and responsive due to the evolving government advice around COVID-19, and the fact that circumstances will change.
            </p>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="member-declaration" name="member-declaration" required value="1">
              <label class="form-check-label" for="member-declaration">I <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></strong>, agree to this declaration<br><span class="badge bg-dark"><?= $today->format("j F Y") ?></span></label>
              <div class="invalid-feedback">
                You (<?= htmlspecialchars($member['MForename']) ?>) must agree to this declaration to proceed.
              </div>
            </div>

            <?php if ($parent && $age < 18) { ?>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="parent-declaration" name="parent-declaration" required value="1">
                <label class="form-check-label" for="parent-declaration">As <?= htmlspecialchars($member['MForename']) ?> is under 18 (aged <?= htmlspecialchars($age) ?>), I <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($parent['Forename'], $parent['Surname'])) ?></strong>, also agree to this declaration, <?= $today->format("j F Y") ?></label>
                <div class="invalid-feedback">
                  You (<?= htmlspecialchars($parent['Forename']) ?>) must agree to this declaration to proceed.
                </div>
              </div>
            <?php } ?>

            <p>
              Please take a lateral flow test within the two days before the start of the competition and <a href="https://www.gov.uk/report-covid19-result" target="_blank">report the result to the NHS</a>. You can <a href="https://www.nhs.uk/conditions/coronavirus-covid-19/testing/regular-rapid-coronavirus-tests-if-you-do-not-have-symptoms/" target="_blank">order rapid lateral flow coronavirus (COVID-19) tests from the NHS</a> for free.
            </p>

            <p>
              For further details of how we process your personal data or your child’s personal data please view our <a href="<?= htmlspecialchars(autoUrl('privacy')) ?>" target="_blank">Privacy Policy</a>.
            </p>

            <p>
              <button type="submit" class="btn btn-success">
                Submit declaration
              </button>
            </p>
          </form>

        </div>
      </div>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/covid-health-screen/form.js');
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
