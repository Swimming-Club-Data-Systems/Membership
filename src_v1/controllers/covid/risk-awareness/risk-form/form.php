<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$getMember = $db->prepare("SELECT MemberID, UserID, MForename, MSurname, DateOfBirth FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $id,
  $tenant->getId(),
]);

$member = $getMember->fetch(PDO::FETCH_ASSOC);

if (!$member) {
  halt(404);
}

if (!$user->hasPermission('Admin') && !$user->hasPermission('Coach') && !$user->hasPermission('Galas')) {
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

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) . ' - COVID Risk Awareness';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/risk-awareness')) ?>">Risk Awareness</a></li>
        <li class="breadcrumb-item active" aria-current="page">Declaration</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?php if (mb_strtoupper(app()->tenant->getKey('ASA_CLUB_CODE')) == 'UOSZ') { ?><?= htmlspecialchars(UOS_RETURN_FORM_NAME) ?><?php } else { ?>COVID-19 Risk Awareness Declaration<?php } ?> <small class="text-muted"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></small>
        </h1>
        <p class="lead mb-0">
          Confirm your awareness of risk before returning to training.
        </p>
      </div>
      <div class="col text-end">
        <?php if (mb_strtoupper(app()->tenant->getKey('ASA_CLUB_CODE')) != 'UOSZ') { ?>
          <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/se.png')) ?>" class="w-50 ms-auto d-none d-lg-flex" alt="Swim England Logo">
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">

    <div class="col-lg-8">
      <form method="post" class="needs-validation" novalidate>

        <?php if (isset($_SESSION['CovidRiskAwarenessError'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>There was a problem saving your COVID-19 Risk Awareness Declaration</strong>
            </p>
            <p class="mb-0">
              <?= htmlspecialchars($_SESSION['CovidRiskAwarenessError']) ?>
            </p>
          </div>
        <?php unset($_SESSION['CovidRiskAwarenessError']);
        } ?>

        <p>
          [I / my child] <?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?> [am / is] returning to training having completed and signed the Health Survey as requested by <?= htmlspecialchars($tenant->getName()) ?>.
        </p>

        <p>
          By signing this declaration, I confirm [I am / my child is] free from any symptoms related to the Covid-19 virus, I understand the main symptoms include:
        </p>

        <ul>
          <li>a high temperature – this means you feel hot to touch on your chest or back</li>
          <li>a new, continuous cough – this means coughing a lot for more than an hour, or three or more coughing episodes in 24 hours</li>
          <li>a loss or change to your sense of smell or taste.</li>
        </ul>

        <p>
          I am also confirming all in my household remain symptom free, and anyone taking me to or from training and attending my training session with me is also symptom free from the virus.
        </p>

        <p>
          By signing this declaration, I confirm that for any future training sessions [I / my child] will only attend in the full knowledge that [I am / my child is] free from any Covid-19 symptoms. In addition, but conversely confirm by signing this declaration that if [I do / my child does] display any symptoms [I / my child] will not attend training for a period of at least 10 days and follow government guidance to self-isolate.
        </p>

        <p>
          I am also confirming all in my household will follow the government guidance on quarantine and self-isolation following any travel abroad.
        </p>

        <p>
          [I / My child] return[s] to training knowing that participation cannot be without risk, I am therefore aware of these risks associated with the Covid-19 virus, but [I still wish / I still wish my child] to participate in club training.
        </p>

        <p>
          I understand the processes and protocols <?= htmlspecialchars($tenant->getName()) ?> have put in place in order to reduce risks and [I / my child] will adhere to these in order to protect [my / my child's] health and the health of other members, staff and other users of the facility.
        </p>

        <p>
          I also understand that the club will have to be flexible and responsive due to the evolving government advice around COVID-19, and the fact that circumstances will change.
        </p>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="member-declaration" name="member-declaration" required value="1">
          <label class="form-check-label" for="member-declaration">I <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></strong>, agree to this declaration<br><span class="badge bg-light">Signed <?= $today->format("j F Y") ?></span></label>
          <div class="invalid-feedback">
            You (<?= htmlspecialchars($member['MForename']) ?>) must agree to this declaration to proceed.
          </div>
        </div>

        <?php if (app()->tenant->getKey('ASA_CLUB_CODE') == 'UOSZ') { ?>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="uosswpc-member-declaration" name="uosswpc-member-declaration" required value="1">
            <label class="form-check-label" for="uosswpc-member-declaration">I <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></strong>, accept that I am swimming at my own risk and won't hold the club accountable if I catch coronavirus (COVID-19).<br><span class="badge bg-light">Signed <?= $today->format("j F Y") ?></span></label>
            <div class="invalid-feedback">
              You (<?= htmlspecialchars($member['MForename']) ?>) must agree to this special UoSSWPC declaration to proceed.
            </div>
          </div>
        <?php } ?>

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
          <button type="submit" class="btn btn-success">
            Submit declaration
          </button>
        </p>
      </form>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
