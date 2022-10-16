<?php

$db = app()->db;
$tenant = app()->tenant;

try {
  $member = new Member($id);
} catch (Exception $e) {
  halt(404);
}

$user = $member->getUser();

$currentUser = app()->user;
if (!$currentUser->hasPermissions(['Admin'])) halt(404);

$date = new DateTime('now', new DateTimeZone('Europe/London'));
$getQualifications = $db->prepare("SELECT `Name`, `ID` FROM `qualifications` WHERE `Show` AND `Tenant` = :tenant AND (`ID` NOT IN (SELECT `Qualification` AS `ID` FROM `qualificationsMembers` WHERE `Member` = :member AND ValidFrom <= :date AND (ValidUntil >= :date OR ValidUntil IS NULL))) ORDER BY `Name` ASC;");
$getQualifications->execute([
  'tenant' => $tenant->getId(),
  'member' => $member->getId(),
  'date' => $date->format('Y-m-d'),
]);
$qualification = $getQualifications->fetch(PDO::FETCH_ASSOC);

$pagetitle = htmlspecialchars('Add a qualification for ' . $member->getFullName());

$date = new DateTime('now', new DateTimeZone('Europe/London'));

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("members") ?>">Members</a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("members/$id") ?>">#<?= htmlspecialchars($member->getId()) ?></a></li>
        <li class="breadcrumb-item"><a href="<?= autoUrl("members/$id/qualifications") ?>">Qualifications</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Add qualification for <?= htmlspecialchars($member->getFullName()) ?>
        </h1>
        <p class="lead mb-0" id="leadDesc">
          Lorem ipsum
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>Error</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']);
      } ?>

      <?php if ($qualification) { ?>
        <form method="post" class="needs-validation" novalidate>

          <div class="mb-3">
            <label class="form-label" for="qualification">Select qualification</label>
            <select class="form-select" name="qualification" id="qualification" required data-ajax-url="<?= htmlspecialchars(autoUrl('qualifications/lookup')) ?>">
              <option selected disabled>Choose qualification</option>
              <?php do { ?>
                <option value="<?= htmlspecialchars($qualification['ID']) ?>"><?= htmlspecialchars($qualification['Name']) ?></option>
              <?php } while ($qualification = $getQualifications->fetch(PDO::FETCH_ASSOC)); ?>
            </select>
            <div class="invalid-feedback">
              Please select a qualification
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="valid-from">Valid since</label>
            <input type="date" name="valid-from" id="valid-from" class="form-control" required value="<?= htmlspecialchars($date->format('Y-m-d')) ?>" max="<?= htmlspecialchars($date->format('Y-m-d')) ?>">
            <div class="invalid-feedback">
              Please enter the date this qualification is valid from
            </div>
          </div>

          <div class="mb-3" id="expires-selector">
            <div class="form-check custom-control-inline">
              <input type="radio" id="expires-yes" name="expires" class="form-check-input" value="yes" checked>
              <label class="form-check-label" for="expires-yes">Qualification expires</label>
            </div>
            <div class="form-check custom-control-inline">
              <input type="radio" id="expires-no" name="expires" class="form-check-input" value="no">
              <label class="form-check-label" for="expires-no">Never expires</label>
            </div>
          </div>

          <div class="mb-3" id="valid-thru-box">
            <label class="form-label" for="valid-to">Valid until</label>
            <input type="date" name="valid-to" id="valid-to" class="form-control" value="<?= htmlspecialchars($date->format('Y-m-d')) ?>" min="<?= htmlspecialchars($date->format('Y-m-d')) ?>">
            <div class="invalid-feedback">
              Please enter the date this qualification is valid from
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="notes">Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="4"></textarea>
          </div>

          <?= \SCDS\CSRF::write(); ?>

          <p>
            <button type="submit" class="btn btn-success">
              Add
            </button>
          </p>
        </form>
      <?php } else { ?>

        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>
              There are no new qualifications available to add for <?= htmlspecialchars($member->getForename()) ?>
            </strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($member->getForename()) ?> either already has all available qualifications, or no qualifications are available.
          </p>
        </div>

      <?php } ?>
    </div>
  </div>

</div>

<script>
  document.getElementById('qualification').addEventListener('change', ev => {
    // Get qualification expiry info
    let qual = ev.target.value;
    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        let response = JSON.parse(this.responseText);
        let expires = document.getElementById('valid-thru-box');
        if (response.expires) {
          document.getElementById('expires-yes').checked = true;
          document.getElementById('valid-to').value = response.date_expires;
          expires.classList.remove('d-none');
        } else {
          document.getElementById('expires-no').checked = true;
          expires.classList.add('d-none');
        }
      }
    };
    let formData = new FormData();
    formData.append('qualification', qual);
    xhttp.open('POST', document.getElementById('qualification').dataset.ajaxUrl, true);
    xhttp.send(formData);
  });

  document.getElementById('expires-selector').addEventListener('change', ev => {
    let expires = document.getElementById('valid-thru-box');
    if (ev.target.value === 'yes') {
      expires.classList.remove('d-none');
    } else {
      expires.classList.add('d-none');
    }
  });
</script>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
