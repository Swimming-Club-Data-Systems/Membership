<?php

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$Extra = new ParsedownExtra();
$Extra->setSafeMode(true);
$search  = array("\n##### ", "\n#### ", "\n### ", "\n## ", "\n# ");
$replace = array("\n###### ", "\n##### ", "\n#### ", "\n### ", "\n## ");
//echo $Extra->text('# Header {.sth}'); # prints: <h1 class="sth">Header</h1>

$db = app()->db;
$tenant = app()->tenant;

$squad = null;
try {
  $squad = Squad::get($id);
} catch (Exception $e) {
  halt(404);
}

$numSwimmers = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Squad = ?");
$numSwimmers->execute([$id]);
$numSwimmers = $numSwimmers->fetchColumn();

$codeOfConduct = $squad->getCodeOfConductMarkdown();
if ($codeOfConduct) {
  $codeOfConduct = str_replace($search, $replace, $codeOfConduct);
  if ($codeOfConduct[0] == '#') {
    $codeOfConduct = '#' . $codeOfConduct;
  }
}

// See if this squad is allowed
$canAccessSquadInfo = false;
$isAllowed = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ? AND Squad = ?");
$isAllowed->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  $id
]);
if ($isAllowed->fetchColumn() > 0) {
  // User cannot access this squad
  $canAccessSquadInfo = true;
}

$swimmers = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent' || $canAccessSquadInfo) {
  $swimmers = $db->prepare("SELECT MemberID id, MForename first, MSurname last, DateOfBirth dob, Forename fn, Surname sn, EmailAddress email, Mobile mob, members.UserID `user` FROM ((members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID) LEFT JOIN users ON members.UserID = users.UserID) WHERE squadMembers.Squad = ? ORDER BY first ASC, last ASC");
  $swimmers->execute([$id]);
}

$coaches = $squad->getCoaches();

// Chart data section start
$getNumSex = $db->prepare("SELECT COUNT(*) FROM members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID WHERE Squad = ? AND Gender = ?");
$getNumSex->execute([$id, 'Male']);
$male = (int) $getNumSex->fetchColumn();
$getNumSex->execute([$id, 'Female']);
$female = (int) $getNumSex->fetchColumn();

$getBirths = $db->prepare("SELECT DateOfBirth FROM members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID WHERE Squad = ?");
$getBirths->execute([$id]);
$agesArray = [];
$timeNow = new DateTime('now', new DateTimeZone('Europe/London'));
while ($dob = $getBirths->fetchColumn()) {
  $timeBirth = new DateTime($dob, new DateTimeZone('Europe/London'));
  $interval = $timeNow->diff($timeBirth);
  $age = (int) $interval->format('%y');
  if (isset($agesArray[$age])) {
    $agesArray[$age] += 1;
  } else {
    $agesArray[$age] = 1;
  }
}
$agesArrayKeys = array_keys($agesArray);
$minAge = $maxAge = 0;
if ($agesArrayKeys) {
  $minAge = min($agesArrayKeys);
  $maxAge = max($agesArrayKeys);
}

$output = [
  'Labels' => [],
  'Data' => []
];

if ($maxAge - $minAge > 10) {
  foreach ($agesArray as $age => $count) {
    $output['Labels'][] = $age . " Year Olds";
    $output['Data'][] = $count;
  }
} else {
  for ($i = $minAge; $i < $maxAge + 1; $i++) {
    $output['Labels'][] = $i . " Year Olds";
    if (isset($agesArray[$i])) {
      $output['Data'][] = (int) $agesArray[$i];
    } else {
      $output['Data'][] = 0;
    }
  }
}

$pie = [
  'labels' => [
    'Male',
    'Female'
  ],
  'datasets' => [[
    'label' => $squad->getName() . ' Split',
    'data' => [$male, $female],
    'backgroundColor' => chartColours(2)
  ]]
];

$bar = [
  'labels' => $output['Labels'],
  'datasets' => [[
    'label' => $squad->getName() . ' Squad',
    'data' => $output['Data'],
    'backgroundColor' => chartColours(sizeof($output['Data']))
  ]]
];


// Chart data section end

$pagetitle = htmlspecialchars($squad->getName());

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("squads") ?>">Squads</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($squad->getName()) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($squad->getName()) ?>
        </h1>
        <p class="lead mb-0">
          This squad has <?= htmlspecialchars($numSwimmers) ?> members
        </p>
      </div>
      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <div class="col text-sm-end">
          <a href="<?= htmlspecialchars(autoUrl("squads/" . $id . "/edit")) ?>" class="btn btn-dark-l btn-outline-light-d">Edit squad</a>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h2>About this squad</h2>
      <dl class="row">
        <dt class="col-sm-3">Monthly fee</dt>
        <dd class="col-sm-9">&pound;<?= htmlspecialchars($squad->getFee(false)) ?></dd>

        <dt class="col-sm-3">Squad coach<?php if (sizeof($coaches) != 1) { ?>es<?php } ?></dt>
        <dd class="col-sm-9">
          <ul class="list-unstyled mb-0">
            <?php for ($i = 0; $i < sizeof($coaches); $i++) { ?>
              <li><strong><?= htmlspecialchars($coaches[$i]->getFullName()) ?></strong>, <?= htmlspecialchars($coaches[$i]->getType()) ?></li>
            <?php } ?>
            <?php if (sizeof($coaches) == 0) { ?>
              <li>None assigned</li>
            <?php } ?>
          </ul>
        </dd>

        <dt class="col-sm-3">Squad timetable</dt>
        <dd class="col-sm-9 text-truncate">
          <?php if ($squad->getTimetableUrl()) { ?>
            <a href="<?= htmlspecialchars($squad->getTimetableUrl()) ?>" target="_blank">
              <?= htmlspecialchars(trim(str_replace(['https://www.', 'http://www.', 'http://', 'https://'], '', $squad->getTimetableUrl()), '/')) ?>
            </a>
          <?php } else { ?>
            <a href="<?= htmlspecialchars(autoUrl('sessions?squad=' . urlencode($id))) ?>" target="_blank">
              <?= htmlspecialchars(trim(str_replace(['https://www.', 'http://www.', 'http://', 'https://'], '', autoUrl('sessions?squad=' . urlencode($id))), '/')) ?>
            </a>
          <?php } ?>
        </dd>
      </dl>

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') { ?>
        <?php $members = $squad->getMembers(); ?>
        <h2><?= htmlspecialchars($squad->getName()) ?> Members</h2>
        <?php if (sizeof($members) > 0) { ?>
          <div class="list-group mb-3">
            <?php foreach ($members as $member) { ?>
              <a href="<?= autoUrl("members/" . $member->getId()) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <div>
                  <div><?= htmlspecialchars($member->getFullName()) ?></div><?php if ($member->showGender()) { ?><div class=""><em><?= htmlspecialchars($member->getGenderIdentity()) ?>, <?= htmlspecialchars($member->getGenderPronouns()) ?></em></div><?php } ?>
                </div>
                <span class="badge bg-primary rounded-pill rounded">Age <?= htmlspecialchars(($member->getAge())) ?></span>
              </a>
            <?php } ?>
          </div>
        <?php } else { ?>
          <div class="alert alert-info">
            <p class="mb-0">
              <strong>No members to display</strong>
            </p>
          </div>
        <?php } ?>
      <?php } ?>

      <?php if ($canAccessSquadInfo) { ?>
        <?php $members = $squad->getMembers(); ?>
        <h2><?= htmlspecialchars($squad->getName()) ?> Members</h2>
        <?php if (sizeof($members) > 0) { ?>
          <ul class="list-group mb-3 accordion" id="memberContactAccordion">
            <?php foreach ($members as $member) { ?>
              <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                  <?= htmlspecialchars($member->getFullName()) ?>
                  <span class="badge bg-primary rounded-pill rounded">Age <?= htmlspecialchars(($member->getAge())) ?></span>
                </div>

                <?php if ($user = $member->getUser()) { ?>
                  <p class="mb-0 mt-3">
                    <a data-bs-toggle="collapse" href="#details-<?= htmlspecialchars($member->getId()) ?>" role="button" aria-expanded="false" aria-controls="details-<?= htmlspecialchars($member->getId()) ?>" class="btn btn-primary">Show contact details</a>
                  </p>
                  <div class="collapse" id="details-<?= htmlspecialchars($member->getId()) ?>" data-parent="#memberContactAccordion">
                    <div class="cell mb-0 mt-3">
                      <p>Contact <?= htmlspecialchars($member->getFullName()) ?> by email or phone.</p>
                      <dl class="row mb-0">
                        <dt class="col-md-3">Email</dt>
                        <dd class="col-md-9"><a href="<?= htmlspecialchars("mailto:" . $user->getEmail()) ?>"><?= htmlspecialchars($user->getEmail()) ?></a></dd>

                        <dt class="col-md-3">Phone</dt>
                        <dd class="col-md-9 mb-0">
                          <?php try {
                            $mobile = PhoneNumber::parse((string) $user->getMobile()); ?>
                            <a href="<?= htmlspecialchars($mobile->format(PhoneNumberFormat::RFC3966)) ?>"><?= htmlspecialchars($mobile->format(PhoneNumberFormat::NATIONAL)) ?></a>
                          <?php } catch (PhoneNumberParseException | Exception $e) { ?>
                            The user's phone number is not valid
                          <?php } ?>
                        </dd>
                      </dl>
                    </div>
                  </div>
                <?php } ?>
              </li>
            <?php } ?>
          </ul>
        <?php } else { ?>
          <div class="alert alert-info">
            <p class="mb-0">
              <strong>No members to display</strong>
            </p>
          </div>
        <?php } ?>
      <?php } ?>

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent" && $numSwimmers > 0) { ?>
        <h2>Sex Split</h2>
        <canvas class="mb-3" id="sexSplit" data-data="<?= htmlspecialchars(json_encode($pie)) ?>"></canvas>

        <h2>Age Distribution</h2>
        <p class="lead">The age distribution chart shows the number of swimmers of each age in this squad.</p>
        <canvas class="mb-3" id="ageDistribution" data-data="<?= htmlspecialchars(json_encode($bar)) ?>"></canvas>
      <?php } ?>

      <?php if ($codeOfConduct != null) { ?>
        <h2>Code of conduct for <?= htmlspecialchars($squad->getName()) ?></h2>

        <?= $Extra->text($codeOfConduct) ?>
      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent") {
  // $footer->addJS("js/Chart.min.js");
  if ($numSwimmers > 0) {
    $footer->addJS("js/squads/squad-charts.js");
  }
}
$footer->render();
