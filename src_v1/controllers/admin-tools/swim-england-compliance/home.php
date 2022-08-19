<?php

$pagetitle = "Swim England Compliance Centre";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Swimmark</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Swim England Compliance Centre</h1>
      <p class="lead">Welcome to the compliance dashboard.</p>

      <p>
        Our new member onboarding and membership renewal tools require you to provide additional documentation to ensure your onboarding flow complies with Swim England's Wavepower requirements. Additional documentation required is based on the Swim England Recommended new member checklist.
      </p>

      <form method="post" class="needs-validation" novalidate>

        <h2>Signposting</h2>

        <div class="mb-3">
          <label for="web-address" class="form-label">Web address</label>
          <input type="url" class="form-control" id="web-address" name="web-address" value="<?= htmlspecialchars(app()->tenant->getWebsite()) ?>" readonly>
        </div>

        <div class="mb-3">
          <label for="facebook" class="form-label">Facebook page</label>
          <div class="input-group">
            <div class="input-group-text">
              https://www.facebook.com/
            </div>
            <input type="url" class="form-control" id="facebook" name="facebook" value="<?= htmlspecialchars(app()->tenant->getKey('FACEBOOK_PAGE')) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label for="twitter" class="form-label">Twitter Username</label>
          <div class="input-group">
            <div class="input-group-text">
              https://twitter.com/
            </div>
            <input type="text" class="form-control" id="twitter" name="twitter" value="<?= htmlspecialchars(app()->tenant->getKey('TWITTER_ACCOUNT')) ?>">
          </div>
        </div>

        <p class="mb-2">
          Do you have a noticeboard? Tell us how people can find it.
        </p>
        <div class="mb-3">
          <label for="noticeboard" class="form-label">Noticeboard locations</label>
          <textarea class="form-control" id="noticeboard" name="noticeboard"><?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('NOTICEBOARD_LOCATIONS')) ?></textarea>
        </div>

        <p class="mb-2">
          Tell us where your members can find the latest news and updates
        </p>
        <div class="mb-3">
          <label for="where-to-find-updates" class="form-label">Members can find news where?</label>
          <textarea class="form-control" id="where-to-find-updates" name="where-to-find-updates"><?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('NEWS_LOCATIONS')) ?></textarea>
        </div>

        <p>
          We will automatically signpost your organisation's privacy policy. Including your privacy policy is mandatory for your use of this software.
        </p>

        <p>
          We will automatically signpost the Swim England website and Wavepower.
        </p>

        <p>
          We will automatically signpost the Swim England/NSPCC SwimLine service.
        </p>

        <h2>Welfare Officer</h2>

        <p>
          We'll ensure your members know how to contact your welfare officer.
        </p>

        <div class="mb-3">
          <label for="welfare-name" class="form-label">Welfare Officer Name</label>
          <input type="text" class="form-control" id="welfare-name" name="welfare-name" value="<?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('WELFARE_NAME')) ?>" required>
        </div>

        <p>
          You must provide at least one of the following contact options for your welfare officer.
        </p>

        <div class="mb-3">
          <label for="welfare-email" class="form-label">Welfare Officer Email</label>
          <input type="email" class="form-control" id="welfare-email" name="welfare-email" value="<?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('WELFARE_EMAIL')) ?>">
        </div>

        <div class="mb-3">
          <label for="welfare-phone" class="form-label">Welfare Officer Phone</label>
          <input type="tel" class="form-control" id="welfare-phone" name="welfare-phone" value="<?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('WELFARE_PHONE')) ?>">
        </div>

        <h2>Complaints process</h2>

        <div class="mb-3">
          <label for="complaints-process" class="form-label">Explain your complaints and disciplinary processes</label>
          <textarea class="form-control" id="complaints-process" name="complaints-process"><?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('COMPLAINTS_PROCESS')) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="complaints-name" class="form-label">Officer responsible for complaints name</label>
          <input type="text" class="form-control" id="complaints-name" name="complaints-name" value="<?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('COMPLAINTS_OFFICER')) ?>">
        </div>

        <p>
          You must provide at least one of the following contact options for your club officer responsible for complaints.
        </p>

        <div class="mb-3">
          <label for="complaints-email" class="form-label">Officer responsible for complaints email</label>
          <input type="email" class="form-control" id="complaints-email" name="complaints-email" value="<?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('COMPLAINTS_EMAIL')) ?>">
        </div>

        <div class="mb-3">
          <label for="complaints-phone" class="form-label">Officer responsible for complaints phone</label>
          <input type="tel" class="form-control" id="complaints-phone" name="complaints-phone" value="<?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('COMPLAINTS_PHONE')) ?>">
        </div>

        <h2>Facility information</h2>

        <div class="mb-3">
          <label for="facility-info" class="form-label">Information about the facilities you hire - include information about the changing facilities and any relevant rules and regulations applicable to the facility e.g. the use of mobile devices.</label>
          <textarea class="form-control" id="facility-info" name="facility-info"><?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('FACILITY_INFORMATION')) ?></textarea>
        </div>

        <h2>SwimMark Status</h2>

        <?php ($status = app()->tenant->getSwimEnglandComplianceValue('SWIMMARK_STATUS')); ?>

        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="swimmark" id="swimmark-none" value="none" required <?php if ($status == 'none' || $status == null){ ?>checked<?php } ?>>
              Not accredited
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="swimmark" id="swimmark-sa" value="stronger-affiliation" <?php if ($status == 'stronger-affiliation'){ ?>checked<?php } ?>>
            <label class="form-check-label" for="swimmark-sa">
              Swim England Stronger Affiliation
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="swimmark" id="swimmark-essential" value="essential" <?php if ($status == 'essential'){ ?>checked<?php } ?>>
            <label class="form-check-label" for="swimmark-essential">
              SwimMark Essential
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="swimmark" id="swimmark-network" value="network" <?php if ($status == 'network'){ ?>checked<?php } ?>>
            <label class="form-check-label" for="swimmark-network">
              SwimMark Network
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="swimmark" id="swimmark-performance" value="performance" <?php if ($status == 'performance'){ ?>checked<?php } ?>>
            <label class="form-check-label" for="swimmark-performance">
              SwimMark Performance
            </label>
          </div>
        </div>

        <div class="mb-3">
          <label for="swimmark-start" class="form-label">Accreditation start date</label>
          <input type="date" class="form-control" id="swimmark-start" name="swimmark-start" value="<?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('SWIMMARK_START')) ?>">
        </div>

        <div class="mb-3">
          <label for="swimmark-end" class="form-label">Accreditation end date</label>
          <input type="date" class="form-control" id="swimmark-end" name="swimmark-end" value="<?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('SWIMMARK_END')) ?>">
        </div>

        <h2>Volunteering opportunities</h2>

        <div class="mb-3">
          <label for="volunteering-opportunities" class="form-label">Give information on volunteering opportunities for adults and children and how to apply</label>
          <textarea class="form-control" id="volunteering-opportunities" name="volunteering-opportunities"><?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('VOLUNTEERING_INFORMATION')) ?></textarea>
        </div>

        <h2>Youth engagement</h2>

        <div class="mb-3">
          <label for="youth-engagement" class="form-label">Explain how members under the age of 18 can contribute to <?= htmlspecialchars(app()->tenant->getName()) ?>. For example youth forums or groups.</label>
          <textarea class="form-control" id="youth-engagement" name="youth-engagement"><?= htmlspecialchars(app()->tenant->getSwimEnglandComplianceValue('YOUTH_ENGAGEMENT_INFORMATION')) ?></textarea>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Save
          </button>
        </p>
      </form>

    </div>

    <div class="col">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
      echo $list->render('admin-swimmark');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation');
$footer->render();
