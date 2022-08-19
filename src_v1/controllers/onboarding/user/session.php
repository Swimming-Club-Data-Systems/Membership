<?php

use SCDS\CSRF;

$user = app()->user;
$db = app()->db;
$tenant = app()->tenant;

$session = \SCDS\Onboarding\Session::retrieve($id);

$states = [
  'not_ready' => 'Not ready',
  'pending' => 'Pending',
  'in_progress' => 'In progress',
  'complete' => 'Complete',
];

$pagetitle = "Onboarding Session";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('onboarding')) ?>">Onboarding</a></li>
        <li class="breadcrumb-item active" aria-current="page">New</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars(app()->tenant->getName()) ?> Onboarding
        </h1>
        <p class="lead mb-0">
          Onboarding is the replacement for assisted registration.
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <form method="post" class="needs-validation" id="form" novalidate>

        <div class="mb-3">
          <label for="user-name" class="form-label">Member/Parent Name</label>
          <input type="text" class="form-control" id="user-name" name="user-name" value="<?= htmlspecialchars($session->getUser()->getName()) ?>" readonly>
        </div>

        <div class="mb-3">
          <label for="session-id" class="form-label">Session ID</label>
          <input type="text" class="form-control" id="session-id" name="session-id" value="<?= htmlspecialchars($session->id) ?>" readonly>
        </div>

        <div class="mb-3">
          <label for="creator-name" class="form-label">Onboarding Creator Name</label>
          <input type="text" class="form-control" id="creator-name" name="creator-name" value="<?= htmlspecialchars($session->getCreator()->getName()) ?>" readonly>
        </div>

        <div class="mb-3">
          <label for="start-date" class="form-label">Start Date</label>
          <input type="date" class="form-control" id="start-date" name="start-date" value="<?= htmlspecialchars($session->start->format('Y-m-d')) ?>" required>
        </div>

        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="charge-fees" id="charge-fees-yes" <?php if ($session->chargeOutstanding) { ?>checked<?php } ?> value="1" data-toggle="pro-rata-box">
            <label class="form-check-label" for="charge-fees-yes">
              Charge fees up to first Direct Debit date
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="charge-fees" id="charge-fees-no" <?php if (!$session->chargeOutstanding) { ?>checked<?php } ?> value="0" data-toggle="pro-rata-box">
            <label class="form-check-label" for="charge-fees-no">
              Ignore missed fees
            </label>
          </div>
        </div>

        <div class="collapse <?php if ($session->chargeOutstanding) { ?>show<?php } ?>" id="pro-rata-box">
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="charge-pro-rata" id="charge-pro-rata-yes" <?php if ($session->chargeProRata) { ?>checked<?php } ?>>
              <label class="form-check-label" for="charge-pro-rata-yes">
                Charge pro-rata amount
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="charge-pro-rata" id="charge-pro-rata-no" <?php if (!$session->chargeProRata) { ?>checked<?php } ?>>
              <label class="form-check-label" for="charge-pro-rata-no">
                Charge for full months
              </label>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label for="welcome-text" class="form-label">Welcome Text</label>
          <textarea class="form-control" id="welcome-text" name="welcome-text" rows="5"><?= htmlspecialchars($session->welcomeText) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="status" class="form-label">Onboarding status</label>
          <select class="form-select" name="status" id="status" <?php if ($session->status == 'not_ready') { ?>disabled<?php } ?>>
            <?php foreach ($states as $key => $value) { ?>
              <option <?php if ($session->status == $key) { ?>selected<?php } ?> value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="has-due-date" id="has-due-date-yes" <?php if ($session->dueDate) { ?>checked<?php } ?> value="1" data-toggle="due-date-box">
            <label class="form-check-label" for="has-due-date-yes">
              Set due date
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="has-due-date" id="has-due-date-no" <?php if (!$session->dueDate) { ?>checked<?php } ?> value="0" data-toggle="due-date-box">
            <label class="form-check-label" for="has-due-date-no">
              Open ended
            </label>
          </div>
        </div>

        <div class="collapse <?php if ($session->dueDate) { ?>show<?php } ?>" id="due-date-box">
          <div class="mb-3">
            <label for="due-date" class="form-label">Due Date</label>
            <input type="date" class="form-control" id="due-date" name="due-date" value="<?php if ($session->dueDate) { ?><?= htmlspecialchars($session->dueDate->format('Y-m-d')) ?><?php } ?>">
          </div>
        </div>

        <div class="mb-3">
          <p class="mb-2">
            Required tasks
          </p>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="task-model-forms" name="task-model-forms" checked>
            <label class="form-check-label" for="task-model-forms">
              Model forms
            </label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="task-fees" name="task-fees" checked>
            <label class="form-check-label" for="task-fees">
              Registration fees
            </label>
          </div>
        </div>

        <p class="mb-2">
          Onboarding members
        </p>

        <ul class="mb-3">
          <?php foreach ($session->members as $member) { ?>
            <li><?= htmlspecialchars($member->firstName . ' ' . $member->lastName) ?></li>
          <?php } ?>
        </ul>
      </form>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->addJs('js/onboarding/admin/session.js');
$footer->render();
