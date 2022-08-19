<?php

$db = app()->db;
$tenant = app()->tenant;

$getUser = $db->prepare("SELECT Forename, Surname, RR FROM users INNER JOIN `permissions` ON users.UserID = `permissions`.`User` WHERE Tenant = ? AND UserID = ? AND `permissions`.`Permission` = 'Parent';");
$getUser->execute([
  $tenant->getId(),
  $person
]);

$user = $getUser->fetch(PDO::FETCH_ASSOC);

if ($user == null) {
  halt(404);
}

$renewalAvailable = true;
try {
  include 'GetRenewal.php';
} catch (Exception $e) {
  $renewalAvailable = false;
}

$pagetitle = 'Allow Opt Out';

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>Allow a Direct Debit Opt Out</h1>
      <form method="post">
        <p class="lead"><?php if ($user['RR']) { ?>Complete registration without setting up a direct debit<?php } else { ?>Complete renewal without setting up a direct debit<?php } ?></p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorInvalidRequest']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorInvalidRequest']) { ?>
          <div class="alert alert-warning">
            <strong>We couldn't verify you had the authority to do that</strong>
          </div>
        <?php }
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorInvalidRequest']); ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorNoReg']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorNoReg']) { ?>
          <div class="alert alert-warning">
            <strong>There was no open registration or renewal</strong>
          </div>
        <?php }
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorNoReg']); ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Successful']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Successful']) { ?>
          <div class="alert alert-success">
            <strong>We've marked the renewal/registration as complete and bypassed the direct debit requirement</strong>
          </div>
        <?php }
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['Successful']); ?>

        <?php if ($renewalAvailable) { ?>

          <p>Allow <?= htmlspecialchars(\SCDS\Formatting\Names::format($user['Forename'], $user['Surname'])) ?> to not use Direct Debit.</p>

          <?= \SCDS\FormIdempotency::write() ?>
          <?= \SCDS\CSRF::write() ?>

          <p>
            <button type="submit" class="btn btn-success">
              Allow
            </button>
          </p>
        <?php } else { ?>

          <p>There's not currently a registration or renewal open for <?= htmlspecialchars(\SCDS\Formatting\Names::format($user['Forename'], $user['Surname'])) ?>, so you're not able to authorise a direct debit opt out.</p>

        <?php } ?>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
