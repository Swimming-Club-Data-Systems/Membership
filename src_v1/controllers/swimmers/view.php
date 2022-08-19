<?php

/**
 * New single member view and edit page
 */

$db = app()->db;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

try {
  $member = new Member($id);
} catch (Exception $e) {
  halt(404);
}

$user = $member->getUser();

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && (!$user || $user->getId() != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
  halt(404);
}

$manageSquads = false;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin' || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Coach') {
  $manageSquads = true;
}

$squads = $member->getSquads();

// Memberships held
$today = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d');
$getMemberships = $db->prepare("SELECT clubMembershipClasses.Name membershipName, clubMembershipClasses.Description description, memberships.Amount amount, memberships.PaymentInfo paymentInfo, membershipYear.Name yearName, memberships.StartDate starts, memberships.EndDate ends FROM memberships INNER JOIN clubMembershipClasses ON memberships.Membership = clubMembershipClasses.ID INNER JOIN membershipYear ON memberships.Year = membershipYear.ID WHERE Member = ? AND memberships.StartDate <= ? AND memberships.EndDate >= ? ORDER BY starts ASC, membershipName ASC");
$getMemberships->execute([
  $id,
  $today,
  $today,
]);
$membership = $getMemberships->fetch(PDO::FETCH_OBJ);

$pagetitle = htmlspecialchars($member->getFullName());

$pageHead = [
  'body' => [
    'data-bs-spy="scroll"',
    'data-bs-target="#member-page-menu"'
  ]
];

$pbs = null;
// try {
//   $httpClient = new GuzzleHttp\Client();
//   $res = $httpClient->request('GET', 'https://dev.myswimmingclub.uk/bsdbc/members/' . $member->getSwimEnglandNumber() . '/times', []);
//   if ($res->getStatusCode() == "200") {
//     $pbs = json_decode($res->getBody());
//   }
// } catch (GuzzleHttp\Exception\ClientException | GuzzleHttp\Exception\ServerException $e) {
//   // 404 or something
// }

$extraFees = $db->prepare("SELECT extras.ExtraID, ExtraName, ExtraFee, `Type` FROM extrasRelations INNER JOIN extras ON extras.ExtraID = extrasRelations.ExtraID WHERE extrasRelations.MemberID = ?");
$extraFees->execute([
  $id,
]);

$getLatestCovidSurveyCompletion = $db->prepare("SELECT `ID`, `DateTime`, `OfficerApproval`, `ApprovedBy`, `Forename`, `Surname` FROM covidHealthScreen LEFT JOIN users ON covidHealthScreen.ApprovedBy = users.UserID WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");
$getLatestCovidSurveyCompletion->execute([
  $id
]);
$latestCovidSurvey = $getLatestCovidSurveyCompletion->fetch(PDO::FETCH_ASSOC);

$getLatestCovidRAForm = $db->prepare("SELECT `ID`, `DateTime`, `MemberAgreement`, `Guardian`, `Forename`, `Surname` FROM covidRiskAwareness LEFT JOIN users ON users.UserID = covidRiskAwareness.Guardian WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");
$getLatestCovidRAForm->execute([
  $id
]);
$latestCovidRAForm = $getLatestCovidRAForm->fetch(PDO::FETCH_ASSOC);

$fluidContainer = true;
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-fluid">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("members") ?>">Members</a></li>
        <li class="breadcrumb-item active" aria-current="page">#<?= htmlspecialchars($member->getId()) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($member->getFullName()) ?>
        </h1>
        <p class="lead mb-0" id="leadDesc">
          Member
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <div class="text-lg-end col-lg">
        <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
          <p>
            <a href="<?= htmlspecialchars(autoUrl("members/" . $id . "/edit")) ?>" class="btn btn-success">
              Edit basic details
            </a>
          </p>
        <?php } ?>
        <?php if ($user && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') { ?>
          <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Quick actions
            </button>
            <div class="dropdown-menu dropdown-menu-lg-end" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("members/" . $id . "/enter-gala")) ?>">Enter a gala</a>
              <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("users/" . $user->getId())) ?>">View linked user</a>
              <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("members/" . $id . "/contact-parent")) ?>">Email user/parent/guardian</a>
              <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Galas') { ?>
                <button class="dropdown-item" id="new-move-dropdown" type="button">New squad move</button>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("members/" . $id . "/parenthelp")) ?>">Print access key</a>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">

  <div class="row justify-content-between">
    <div class="col-md-4 col-lg-3 col-xl-3">
      <div class="position-sticky top-3 card mb-3">
        <div class="card-header">
          Jump to
        </div>
        <div class="list-group list-group-flush" id="member-page-menu">
          <a href="#basic-information" class="list-group-item list-group-item-action">
            Basic information
          </a>
          <a href="#medical-details" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            Medical notes
            <i class="fa fa-fw fa-ambulance" aria-hidden="true"></i>
          </a>
          <a href="#emergency-contacts" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            Emergency contacts
            <i class="fa fa-fw fa-phone" aria-hidden="true"></i>
          </a>
          <a href="#photography-permissions" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            Photography consent
            <i class="fa fa-fw fa-camera" aria-hidden="true"></i>
          </a>
          <a href="#squads" class="list-group-item list-group-item-action">
            Squad<?php if (sizeof($squads) != 1) { ?>s<?php } ?>
          </a>
          <a href="#qualifications" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            Qualifications
            <i class="fa fa-fw fa-certificate" aria-hidden="true"></i>
          </a>
          <a href="#extras" class="list-group-item list-group-item-action">
            Extra fees
          </a>
          <a href="#personal-bests" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            Personal best times
            <i class="fa fa-fw fa-clock-o" aria-hidden="true"></i>
          </a>
          <a href="#membership-details" class="list-group-item list-group-item-action">
            Membership details
          </a>
          <a href="#other-details" class="list-group-item list-group-item-action">
            Other details
          </a>
        </div>
      </div>
    </div>

    <div class="col">
      <!-- Basic information -->
      <h2 id="basic-information">Basic information</h2>
      <dl class="row">
        <div class="col-6">
          <dt class="text-truncate">
            Date of birth
          </dt>
          <dd>
            <?= htmlspecialchars($member->getDateOfBirth()->format("j F Y")) ?>
          </dd>
        </div>

        <div class="col-6">
          <dt class="text-truncate">
            Country
          </dt>
          <dd>
            <?= htmlspecialchars($member->getCountry()) ?>
          </dd>
        </div>

        <div class="col-6">
          <dt class="text-truncate">
            <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership #
          </dt>
          <dd>
            <a href="<?= htmlspecialchars('https://www.swimmingresults.org/biogs/biogs_details.php?tiref=' . $member->getSwimEnglandNumber()) ?>">
              <?= htmlspecialchars($member->getSwimEnglandNumber()) ?>
            </a>
          </dd>
        </div>

        <div class="col-6">
          <dt class="text-truncate">
            <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership
          </dt>
          <dd>
            <?= htmlspecialchars($member->getGoverningBodyCategoryName()) ?>
          </dd>
        </div>

        <div class="col-6">
          <dt class="text-truncate">
            Sex (for the purposes of competition) <a tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus" title="" data-bs-content="The sex <?= htmlspecialchars($member->getForename()) ?> competes under, even if this is not the same as their gender identity." data-original-title="What does this mean?"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
          </dt>
          <dd>
            <?= htmlspecialchars($member->getSex()) ?>
          </dd>
        </div>

        <?php if ($member->showGender()) { ?>
          <div class="col-6">
            <dt class="text-truncate">
              Gender Identity <a tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus" title="" data-bs-content="Your gender identity is a way to describe how you feel about your gender. You might identify your gender as a boy or a girl or something different. This is different from your sex, which is related to your physical body and biology." data-original-title="What is gender identity?"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
            </dt>
            <dd>
              <?= htmlspecialchars($member->getGenderIdentity()) ?>
            </dd>
          </div>

          <div class="col-6">
            <dt class="text-truncate">
              Gender Pronouns <a tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus" title="" data-bs-content="The words we use to refer to someone like, ‘he’, ‘she’ and ‘they’. Members can choose their pronouns so that club staff don't misgender them." data-original-title="What are pronouns?"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
            </dt>
            <dd>
              <?= htmlspecialchars($member->getGenderPronouns()) ?>
            </dd>
          </div>
        <?php } ?>
      </dl>

      <!-- <p>
        <button class="btn btn-success">
          Edit basic details
        </button>
      </p> -->

      <hr>

      <h2 id="covid-screen">COVID-19 Health Survey</h2>

      <?php if ($latestCovidSurvey) {
        $time = new DateTime($latestCovidSurvey['DateTime'], new DateTimeZone('UTC'));
        $time->setTimezone(new DateTimeZone('Europe/London'));
      ?>
        <p>
          Latest submission <?= htmlspecialchars($time->format('H:i, j F Y')) ?><br>
          <?php if (bool($latestCovidSurvey['OfficerApproval'])) { ?>
            <span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Approved by <?= htmlspecialchars(\SCDS\Formatting\Names::format($latestCovidSurvey['Forename'], $latestCovidSurvey['Surname'])) ?></span>
          <?php } else if (!bool($latestCovidSurvey['OfficerApproval']) && $latestCovidSurvey['ApprovedBy']) { ?>
            <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> Rejected by <?= htmlspecialchars(\SCDS\Formatting\Names::format($latestCovidSurvey['Forename'], $latestCovidSurvey['Surname'])) ?></span>
          <?php } else if (!bool($latestCovidSurvey['OfficerApproval'])) { ?>
            <span class="text-warning"><i class="fa fa-minus-circle" aria-hidden="true"></i> Awaiting approval</span>
          <?php } ?>
        </p>

        <p>
          <a href="<?= htmlspecialchars(autoUrl('covid/health-screening/members/' . $id)) ?>" class="btn btn-success">
            View all submissions
          </a>
        </p>
      <?php } else { ?>
        <p class="mb-0">
          No survey submitted
        </p>
      <?php } ?>

      <hr>

      <h2 id="covid-risk-awareness">COVID-19 Risk Awareness Declaration</h2>

      <?php if ($latestCovidRAForm) {
        $time = new DateTime($latestCovidRAForm['DateTime'], new DateTimeZone('UTC'));
        $time->setTimezone(new DateTimeZone('Europe/London'));
      ?>
        <?php if (bool($latestCovidRAForm['MemberAgreement'])) { ?>
          <span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Signed at <?= htmlspecialchars($time->format('H:i, j F Y')) ?><?php if ($latestCovidRAForm['Guardian']) { ?> with <?= htmlspecialchars(\SCDS\Formatting\Names::format($latestCovidRAForm['Forename'], $latestCovidRAForm['Surname'])) ?> as parent/guardian<?php } ?></span>
        <?php } else { ?>
          <span class="text-warning"><i class="fa fa-minus-circle" aria-hidden="true"></i> A new declaration form is required</span>
        <?php } ?>
      <?php } else { ?>
        <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> No risk awareness declaration submitted</span>
      <?php } ?>

      <hr>

      <!-- Medical details -->
      <h2 id="medical-details">Medical notes</h2>

      <?php $medical = $member->getMedicalNotes(); ?>

      <dl>
        <?php if ($medical->hasMedicalNotes()) { ?>
          <dt class="text-truncate">
            Medical Conditions or Disabilities
          </dt>
          <dd>
            <div class="cell mt-1 mb-0"><?= $medical->getConditions() ?></div>
          </dd>

          <dt class="text-truncate">
            Allergies
          </dt>
          <dd>
            <div class="cell mt-1 mb-0"><?= $medical->getAllergies() ?></div>
          </dd>

          <dt class="text-truncate">
            Medication
          </dt>
          <dd>
            <div class="cell mt-1 mb-0"><?= $medical->getMedication() ?></div>
          </dd>
        <?php } ?>

        <?php if ($member->getAge() < 18) { ?>
          <dt class="text-truncate">
            Consent
          </dt>
          <dd>
            <?= htmlspecialchars($medical->hasConsent()) ?>
          </dd>

          <?php if ($medical->getGpName()) { ?>
            <dt class="text-truncate">
              Name of GP
            </dt>
            <dd>
              <?= htmlspecialchars($medical->getGpName()) ?>
            </dd>
          <?php } ?>

          <?php if ($medical->getGpAddress()) { ?>
            <dt class="text-truncate">
              GP Address
            </dt>
            <dd>
              <?php foreach ($medical->getGpAddress() as $line) { ?>
                <?= htmlspecialchars($line) ?><br>
              <?php } ?>
            </dd>
          <?php } ?>

          <?php if ($medical->getGpPhone()) {
            try {
              $number = PhoneNumber::parse($medical->getGpPhone());

          ?>
              <dt class="text-truncate">
                GP Phone
              </dt>
              <dd>
                <a href="<?= htmlspecialchars($number->format(PhoneNumberFormat::RFC3966)) ?>"><?= htmlspecialchars($number->format(PhoneNumberFormat::INTERNATIONAL)) ?></a>
              </dd>
          <?php
            } catch (PhoneNumberParseException $e) {
              // Ignore
            }
          } ?>
        <?php } ?>
      </dl>

      <?php if (!$medical->hasMedicalNotes()) { ?>

        <p>
          <?= htmlspecialchars($member->getForename()) ?> does not have any specific medical notes to display.
        </p>

      <?php } ?>

      <!-- <p>
        <button class="btn btn-success">
          Edit medical notes
        </button>
      </p> -->

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <p>
          <a href="<?= htmlspecialchars(autoUrl("members/" . $id . "/medical")) ?>" class="btn btn-success">
            Edit medical notes
          </a>
        </p>
      <?php } ?>

      <hr>

      <!-- Emergency details -->
      <h2 id="emergency-contacts">Emergency contact details</h2>

      <?php $emergencyContacts = $member->getEmergencyContacts(); ?>

      <?php if ($emergencyContacts) { ?>

        <p>
          In an emergency, dial one of the contact numbers shown below.
        </p>

        <div class="row">
          <?php foreach ($emergencyContacts as $ec) { ?>

            <div class="col-md-6 col-xl-4">
              <div class="card card-body py-2 px-3 mb-2">
                <div class="row align-items-center">
                  <div class="col-sm-6 col-md-12 col-lg-6">
                    <div class="text-truncate"><strong><?= htmlspecialchars($ec->getName()) ?></strong></div>
                    <div class="text-truncate"><?= htmlspecialchars($ec->getRelation()) ?></div>
                    <div class="mb-2 d-sm-none d-md-flex d-lg-none"></div>
                  </div>
                  <div class="col">
                    <div class="d-grid gap-2">
                      <a href="<?= htmlspecialchars($ec->getRFCContactNumber()) ?>" class="btn btn-success">
                        <i class="fa fa-phone" aria-hidden="true"></i> <?= htmlspecialchars($ec->getNationalContactNumber()) ?>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          <?php } ?>
        </div>

      <?php } else { ?>

        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>No emergency contact details are available.</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($member->getForename()) ?> should not be allowed to train until details have been provided.
          </p>
        </div>

      <?php } ?>

      <hr>

      <!-- Photo permissions -->
      <h2 id="photography-permissions">Photography permissions</h2>
      <?php if ($member->getAge() >= 18) { ?>

        <p>
          <?= htmlspecialchars($member->getForename()) ?> is <?= htmlspecialchars($member->getAge()) ?> so has no photography restrictions in place.
        </p>

      <?php } else { ?>

        <p>
          Club staff are required to follow Swim England's Wavepower as well as relevant club guidance when taking photos or videos.
        </p>

        <div class="row d-flex align-items-stretch">
          <?php $perms = $member->getPhotoPermissions(); ?>

          <?php if (sizeof($perms['allowed']) > 0) { ?>
            <div class="col-xl">
              <div class="card card-body border-success h-100">
                <p class="text-success">
                  <i class="fa fa-check-circle" aria-hidden="true"></i> <strong>Parent/Guardian has allowed</strong>
                </p>

                <ul class="list-unstyled mb-0">
                  <?php foreach ($perms['allowed'] as $text) { ?>
                    <li><?= htmlspecialchars($text->getDescription()) ?></li>
                  <?php } ?>
                </ul>
              </div>
            </div>
          <?php } ?>

          <?php if (sizeof($perms['allowed']) > 0 && sizeof($perms['disallowed']) > 0) { ?>
            <div class="col-12 mb-2 d-block d-xl-none"></div>
          <?php } ?>

          <?php if (sizeof($perms['disallowed']) > 0) { ?>
            <div class="col-xl">
              <div class="card card-body border-danger h-100">
                <p class="text-danger">
                  <i class="fa fa-exclamation-circle" aria-hidden="true"></i> <strong>Parent/Guardian has not allowed</strong>
                </p>

                <ul class="list-unstyled mb-0">
                  <?php foreach ($perms['disallowed'] as $text) { ?>
                    <li><?= htmlspecialchars($text->getDescription()) ?></li>
                  <?php } ?>
                </ul>
              </div>
            </div>
          <?php } ?>
        </div>

        <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>
          <p class="mt-3">
            <a href="<?= htmlspecialchars(autoUrl("members/" . $id . "/edit")) ?>" class="btn btn-success">
              Edit photography preferences
            </a>
          </p>
        <?php } ?>

      <?php } ?>

      <hr>

      <!-- Squad details -->
      <h2 id="squads">Squad<?php if (sizeof($squads) != 1) { ?>s<?php } ?></h2>
      <div id="squadDetails">
        <!-- <p>
        <?= htmlspecialchars($member->getForename()) ?> is a member of <?= htmlspecialchars((new NumberFormatter("en", NumberFormatter::SPELLOUT))->format(sizeof($squads))) ?><?php if (sizeof($squads) != 1) { ?>s<?php } ?>.
      </p>

      <div class="list-group mb-3">
        <?php foreach ($squads as $squad) { ?>
          <a href="<?= htmlspecialchars(autoUrl('squads/' . $squad->getId())) ?>" class="list-group-item list-group-item-action">
            <?= htmlspecialchars($squad->getName()) ?>
          </a>
        <?php } ?>
      </div> -->
      </div>

      <?php if ($manageSquads) { ?>
        <p>
          <button class="btn btn-success" id="new-move-button" data-member="<?= htmlspecialchars($id) ?>" data-squads-url="<?= htmlspecialchars(autoUrl("members/$id/squads.json")) ?>" data-move-url="<?= htmlspecialchars(autoUrl("members/move-squad")) ?>" data-csrf="<?= htmlspecialchars(\SCDS\CSRF::getValue()) ?>">
            Manage squads
          </button>
        </p>

        <p>
          To change whether <?= htmlspecialchars($member->getForename()) ?> pays for a squad, remove them and add them again. The ability to modify this setting properly is coming soon.
        </p>
      <?php } ?>
      <div id="squads-data" data-squads-url="<?= htmlspecialchars(autoUrl("members/$id/squads.json")) ?>"></div>

      <div id="squad-moves-area" data-show-options="<?php if ($manageSquads) { ?>true<?php } else { ?>false<?php } ?>" data-operations-url="<?= htmlspecialchars(autoUrl('members/move-operations')) ?>"></div>

      <hr>

      <h2 id="qualifications">Qualifications</h2>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewQualificationSuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>New qualification added successfully</strong>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewQualificationSuccess']);
      } ?>

      <div id="qualifications-box" data-qualifications-url="<?= htmlspecialchars(autoUrl("members/$id/qualifications/current")) ?>"></div>

      <?php if (app()->user->hasPermissions(['Admin'])) { ?>
        <p>
          <a href="<?= htmlspecialchars(autoUrl("members/$id/qualifications/new")) ?>" class="btn btn-success" id="add-qualification">
            Add qualification
          </a>
        </p>
      <?php } ?>

      <hr>

      <h2 id="extras">Extra Fees</h2>

      <?php if ($extra = $extraFees->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="list-group">
          <?php do { ?>
            <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>
              <div class="list-group-item">
                <?= htmlspecialchars($extra['ExtraName']) ?> <em><?php if ($extra['Type'] == 'Refund') { ?>(Credit) <?php } ?>&pound;<?= (string) \Brick\Math\BigDecimal::of((string) $extra['ExtraFee'])->toScale(2) ?>/month</em>
              </div>
            <?php } else { ?>
              <a href="<?= htmlspecialchars(autoUrl('payments/extrafees/' . $extra['ExtraID'])) ?>" class="list-group-item list-group-item-action">
                <?= htmlspecialchars($extra['ExtraName']) ?> <em><?php if ($extra['Type'] == 'Refund') { ?>(Credit) <?php } ?>&pound;<?= (string) \Brick\Math\BigDecimal::of((string) $extra['ExtraFee'])->toScale(2) ?>/month</em>
              </a>
            <?php } ?>
          <?php } while ($extra = $extraFees->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <p>
          <?= htmlspecialchars($member->getForename()) ?> has no additional fees to pay.
        </p>
      <?php } ?>

      <hr>

      <!-- Times -->
      <h2 id="personal-bests">Personal bests</h2>
      <p>
        View <?= htmlspecialchars($member->getForename()) ?>'s personal best times.
      </p>

      <p>
        <a href="<?= htmlspecialchars(autoUrl("members/" . $id . "/times")) ?>" class="btn btn-success">
          See personal bests
        </a>
      </p>

      <?php if ($pbs) { ?>
        <h3><span class="badge bg-info">BETA</span> PBs <small class="text-muted">direct from British Swimming</small></h3>

        <div class="row">
          <div class="col-sm-6">
            <h4>Long Course</h4>
            <ul class="list-unstyled">
              <?php foreach ($pbs->long_course as $eventCode => $event) {
                $swim = $event->swims[0]; ?>
                <li><strong><?= htmlspecialchars($event->event_name) ?></strong><br><?= htmlspecialchars($swim->time) ?> - <?= htmlspecialchars($swim->meet->name) ?> on <?= htmlspecialchars((new DateTime($swim->swim_date, new DateTimeZone('Europe/London')))->format("j F Y")) ?></li>
              <?php } ?>
            </ul>
          </div>
          <div class="col-sm-6">
            <h4>Short Course</h4>
            <ul class="list-unstyled">
              <?php foreach ($pbs->short_course as $eventCode => $event) {
                $swim = $event->swims[0]; ?>
                <li><strong><?= htmlspecialchars($event->event_name) ?></strong><br><?= htmlspecialchars($swim->time) ?> - <?= htmlspecialchars($swim->meet->name) ?> on <?= htmlspecialchars((new DateTime($swim->swim_date, new DateTimeZone('Europe/London')))->format("j F Y")) ?></li>
              <?php } ?>
            </ul>
          </div>
        </div>

      <?php } ?>

      <hr>

      <!-- Membership administration details -->
      <h2 id="membership-details">Membership details</h2>

      <h3>Club Membership</h3>
      <dl class="row">
        <div class="col-6">
          <dt class="text-truncate">
            Club membership category
          </dt>
          <dd>
            <?= htmlspecialchars($member->getClubCategory()) ?>
          </dd>
        </div>

        <div class="col-6">
          <dt class="text-truncate">
            Club pays club membership fees
          </dt>
          <dd>
            <?php if ($member->clubMembershipPaid()) { ?>Yes<?php } else { ?>No, member pays<?php } ?>
          </dd>
        </div>
      </dl>

      <h3><?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership</h3>
      <dl class="row">
        <div class="col-6">
          <dt class="text-truncate">
            <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership Type
          </dt>
          <dd>
            <?= htmlspecialchars($member->getGoverningBodyCategoryName()) ?>
          </dd>
        </div>

        <div class="col-6">
          <dt class="text-truncate">
            Club pays <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> fees
          </dt>
          <dd>
            <?php if ($member->swimEnglandFeesPaid()) { ?>Yes<?php } else { ?>No, member pays<?php } ?>
          </dd>
        </div>


      </dl>

      <h3>Current assigned memberships</h3>
      <?php if ($membership) { ?>

        <?php do {
          $start = new DateTime($membership->starts, new DateTimeZone('Europe/London'));
          $end = new DateTime($membership->ends, new DateTimeZone('Europe/London'));
        ?>
          <div class="card card-body mb-3">
            <h4><?= htmlspecialchars($membership->membershipName) ?> <small class="text-muted">Paid <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($membership->amount), 'GBP')) ?></small></h4>
            <p class="mb-0">
              <?= htmlspecialchars($start->format('j F Y')) ?> - <?= htmlspecialchars($end->format('j F Y')) ?>
            </p>
          </div>
        <?php } while ($membership = $getMemberships->fetch(PDO::FETCH_OBJ)); ?>

        <?php if ($user && app()->user->hasPermission('Admin')) { ?>
          <a href="<?= htmlspecialchars(autoUrl('users/' . $user->getId() . '/new-membership-batch')) ?>">Create a membership batch</a> to add more memberships.
        <?php } ?>

      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong><?= htmlspecialchars($member->getForename()) ?> currently has no assigned memberships</strong>
          </p>
          <?php if (app()->user->hasPermission('Admin')) { ?>
            <?php if ($user) { ?>
              <p class="mb-0">
                <a href="<?= htmlspecialchars(autoUrl('users/' . $user->getId() . '/new-membership-batch')) ?>" class="alert-link">Create a membership batch</a> to add memberships.
              </p>
            <?php } else { ?>
              <p class="mb-0">
                <a href="<?= htmlspecialchars(autoUrl('onboarding/new')) ?>" class="alert-link">Create an onboarding session</a> to create or assign a user and add memberships.
              </p>
            <?php } ?>
          <?php } ?>
        </div>
      <?php } ?>

      <hr>

      <!-- Other notes -->
      <h2 id="other-details">Other details</h2>
      <?php $md = $member->getNotes(); ?>
      <?php if ($md) { ?>
        <?= $md ?>
      <?php } else { ?>
        <p>
          No additional notes for <?= htmlspecialchars($member->getForename()) ?>
        </p>
      <?php } ?>

    </div>
  </div>

</div>

<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-title">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div id="modal-body" class="modal-body"></div>
      <div id="modal-footer" class="modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/members/main.js?v=3');
$footer->useFluidContainer();
$footer->render();
