<?php

$db = app()->db;
$tenant = app()->tenant;

$getMember = $db->prepare("SELECT MForename fn, MSurname sn, ASANumber se, UserID `uid` FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-Member'],
  $tenant->getId()
]);
$member = $getMember->fetch(PDO::FETCH_ASSOC);

if ($member == null) {
  halt(404);
}

$pagetitle = htmlspecialchars("Change your password - " . \SCDS\Formatting\Names::format($member['fn'], $member['sn']));

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Change your password
        </h1>
        <p class="lead mb-0">
          My settings
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <div class="col text-end">
        <p class="mb-0">
          <a href="<?= htmlspecialchars(autoUrl("log-books")) ?>" class="btn btn-dark-l btn-outline-light-d">Log books</a>
          <button type="submit" class="btn btn-success">Save <i class="fa fa-floppy-o" aria-hidden="true"></i></button>
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SetMemberPassError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>We could not update your password.</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['SetMemberPassError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['SetMemberPassError']);
      } ?>

      <p>You can change your password quickly and easily on this page.</p>

      <p>Usual <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> password policies apply - Use 8 characters or more, with at least one lowercase letter, at least one uppercase letter and at least one number</p>

      <form method="post" class="needs-validation" novalidate>
        <?php if (isset($_GET['return'])) { ?>
          <input type="hidden" name="return" value="<?= htmlspecialchars(autoUrl("log-books")) ?>">
        <?php } ?>

        <div class="mb-3">
          <label class="form-label" for="username">Swim England number</label>
          <input type="text" class="form-control" id="username" name="username" aria-describedby="username-help" value="<?= htmlspecialchars($member['se']) ?>" disabled>
          <small id="username-help" class="form-text text-muted">You use your Swim England number alongside your password to sign in</small>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password-1">Password</label>
          <input type="password" class="form-control" id="password-1" name="password-1" aria-describedby="pw-help" autocomplete="new-password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
          <small id="pw-help" class="form-text text-muted">Use 8 characters or more, with at least one lowercase letter, at least one uppercase letter and at least one number</small>
          <div class="invalid-feedback">
            You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password-2">Confirm password</label>
          <input type="password" class="form-control" id="password-2" name="password-2" autocomplete="new-password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
          <div class="invalid-feedback">
            You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
          </div>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Save password
          </button>
        </p>
      </form>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
