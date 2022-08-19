<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

$user = app()->user;
if (!$user->hasPermissions(['Admin'])) halt(404);

$getQualifications = $db->prepare("SELECT `Name`, `Description`, `DefaultExpiry` FROM `qualifications` WHERE `Show` AND `ID` = ? AND `Tenant` = ?");
$getQualifications->execute([
  $id,
  $tenant->getId(),
]);
$qualification = $getQualifications->fetch(PDO::FETCH_ASSOC);

$date = new DateTime('now', new DateTimeZone('Europe/London'));
$getMembers = $db->prepare("SELECT `MForename`, `MSurname`, `MemberID`, `ValidFrom`, `ValidUntil` FROM qualificationsMembers INNER JOIN members ON members.MemberID = qualificationsMembers.Member WHERE Qualification = :id AND ValidFrom <= :date AND (ValidUntil >= :date OR ValidUntil IS NULL) ORDER BY MForename ASC, MSurname ASC");
$getMembers->execute([
  'id' => $id,
  'date' => $date->format('Y-m-d'),
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

if (!$qualification) {
  halt(404);
}

$expiry = json_decode($qualification['DefaultExpiry']);

$markdown = new ParsedownExtra();
$markdown->setSafeMode(true);

$pagetitle = htmlspecialchars($qualification['Name'] . ' - Qualifications');

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">

  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('qualifications')) ?>">Qualifications</a></li>
        <li class="breadcrumb-item active" aria-current="page">View</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1><?= htmlspecialchars($qualification['Name']) ?></h1>
        <p class="lead mb-0">
          Qualification details
        </p>
        <div class="d-lg-none mb-3"></div>
      </div>
      <div class="col-auto ms-lg-auto">
        <div class="btn-group">
          <a href="<?= htmlspecialchars(autoUrl("qualifications/$id/edit")) ?>" class="btn btn-success">Edit</a>
          <!-- <a href="<?= htmlspecialchars(autoUrl("qualifications/$id/remove")) ?>" class="btn btn-danger" title="Remove this qualification for new additions">Remove</a> -->
        </div>
      </div>
    </div>
  </div>

</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewQualificationSuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>New qualification type added successfully</strong>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewQualificationSuccess']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EditQualificationSuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>Changes saved successfully</strong>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['EditQualificationSuccess']);
      } ?>

      <?php if ($qualification['Description']) { ?>
        <h2>Description</h2>
        <?= $markdown->text($qualification['Description']) ?>
      <?php } ?>

      <h2>Members</h2>
      <?php if ($member) { ?>
        <div class="list-group mb-3">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl('members/' . $member['MemberID'] . '#qualifications')) ?>" class="list-group-item list-group-item-action">
              <?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?>
            </a>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-info">
          <p class="mb-0">
            <strong>No members have this qualification</strong>
          </p>
          <p class="mb-0">
            Visit a member's page to add this qualification to their record.
          </p>
        </div>
      <?php } ?>

      <h2>Expiry</h2>
      <?php if ($expiry->expires) { ?>
        <p>This qualification usually expires after <?= htmlspecialchars($expiry->expiry_schedule->value) ?> <?= htmlspecialchars($expiry->expiry_schedule->type) ?></p>
      <?php } else { ?>
        <p>This qualification has no default expiry date.</p>
      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
