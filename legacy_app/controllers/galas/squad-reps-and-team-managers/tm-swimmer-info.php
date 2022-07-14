<?php

// Verify user has access
\SCDS\Can::view('TeamManager', $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id);

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$db = app()->db;
$tenant = app()->tenant;

// GET THE GALA
$getGala = $db->prepare("SELECT GalaName `name`, GalaVenue venue FROM galas WHERE GalaID = ? AND Tenant = ?");
$getGala->execute([
  $id,
  $tenant->getId()
]);
$gala = $getGala->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

// GET SWIMMER INFO
$getSwimmers = $db->prepare("SELECT members.MemberID id, MForename fn, MSurname sn, Website, Social, Noticeboard, FilmTraining, ProPhoto, Conditions, Allergies, Medication, Mobile, Forename pfn, Surname psn, users.UserID FROM ((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) LEFT JOIN memberMedical ON galaEntries.MemberID = memberMedical.MemberID) LEFT JOIN memberPhotography ON galaEntries.MemberID = memberPhotography.MemberID) LEFT JOIN users ON members.UserID = users.UserID) WHERE galaEntries.GalaID = ?");
$getSwimmers->execute([$id]);
$swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC);

$squads = $db->prepare("SELECT SquadName FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE squadMembers.Member = ?");

$markdown = new ParsedownExtra();

$pagetitle = htmlspecialchars($gala['name']) . " Swimmer Information";

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas") ?>">Galas</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas/" . $id) ?>">This Gala</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("galas/" . $id . "/team-manager") ?>">TM Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Swimmers</li>
      </ol>
    </nav>

    <h1><?= htmlspecialchars($gala['name']) ?> swimmer information</h1>
    <p class="lead mb-0">View swimmer medical information and emergency contact details</p>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-md-8">

      <div class="alert alert-danger">
        <p class="mb-0">You <strong>must not disclose data about any swimmers on this page</strong> to other people. If somebody needs access to this data, they will have been granted access to it via their own account.</p>
      </div>

      <?php if ($swimmer == null) { ?>

      <?php } else { ?>

        <ul class="list-group" id="swimmer-details-accordion">
          <?php $i = 0;
          do {
            $squads->execute([
              $swimmer['id']
            ]);
            $s = $squads->fetchAll(PDO::FETCH_COLUMN);

            $member = new Member($swimmer['id']);
            $medical = $member->getMedicalNotes();

            $mobile = null;
            try {
              $mobile = PhoneNumber::parse(strval($swimmer['Mobile']));
            } catch (PhoneNumberParseException | Exception $e) {
              // Do nothing we'll test for null later
            }
          ?>
            <li class="list-group-item <?php if ($i % 2 == 1) { ?>bg-light<?php } ?>">
              <div class="row align-items-center">
                <div class="col-md">
                  <h2><?= htmlspecialchars(\SCDS\Formatting\Names::format($swimmer['fn'], $swimmer['sn'])) ?></h2>
                  <p class="lead mb-0">
                    <?php for ($y = 0; $y < sizeof($s); $y++) {
                      if ($y > 0) {
                    ?>, <?php
                      }
                        ?><?= htmlspecialchars($s[$y]) ?><?php
                                                        } ?>
                  </p>
                  <div class="d-md-none mb-3"></div>
                </div>
                <div class="col text-md-end">
                  <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#swimmer-info-box-<?= $i ?>" role="button" aria-expanded="false" aria-controls="swimmer-info-box-<?= $i ?>">
                    Show information <i class="fa fa-chevron-down" aria-hidden="true"></i>
                  </button>
                </div>
              </div>

              <div class="collapse" id="swimmer-info-box-<?= $i ?>" data-bs-parent="#swimmer-details-accordion">

                <div class="d-md-block mb-3"></div>

                <hr>

                <div class="row">
                  <div class="col-md">
                    <h3>Medical information</h3>

                    <dl>
                      <?php if ($medical->hasMedicalNotes()) { ?>
                        <dt class="text-truncate">
                          Medical Conditions or Disabilities
                        </dt>
                        <dd>
                          <?= $medical->getConditions() ?>
                        </dd>

                        <dt class="text-truncate">
                          Allergies
                        </dt>
                        <dd>
                          <?= $medical->getAllergies() ?>
                        </dd>

                        <dt class="text-truncate">
                          Medication
                        </dt>
                        <dd>
                          <?= $medical->getMedication() ?>
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
                  </div>
                  <div class="col-md">
                    <?php
                    $contacts = new EmergencyContacts($db);
                    $contacts->byParent($swimmer['UserID']);
                    $contactsArray = $contacts->getContacts();
                    ?>
                    <h3>Emergency contacts</h3>
                    <?php if (sizeof($contactsArray) > 0 || $mobile != null) { ?>
                      <ul class="list-group">
                        <li class="list-group-item">
                          <p class="mb-0">
                            <strong><?= htmlspecialchars($swimmer['pfn'] . " " . $swimmer['psn']) ?></strong>
                          </p>
                          <div class="d-grid mt-2">
                            <a href="<?= htmlspecialchars($mobile->format(PhoneNumberFormat::RFC3966)) ?>" class="btn btn-success">
                              <?= htmlspecialchars($mobile->format(PhoneNumberFormat::NATIONAL)) ?>
                            </a>
                          </div>
                        </li>
                        <?php for ($y = 0; $y < sizeof($contactsArray); $y++) { ?>
                          <li class="list-group-item">
                            <p class="mb-0">
                              <strong><?= htmlspecialchars($contactsArray[$y]->getName()) ?></strong>
                            </p>
                            <div class="d-grid mt-2">
                              <a href="<?= htmlspecialchars($contactsArray[$y]->getRFCContactNumber()) ?>" class="btn btn-success">
                                <?= htmlspecialchars($contactsArray[$y]->getNationalContactNumber()) ?>
                              </a>
                            </div>
                          </li>
                        <?php } ?>
                      </ul>
                    <?php } ?>
                  </div>
                </div>
              </div>

            </li>
          <?php $i++;
          } while ($swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>

      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
