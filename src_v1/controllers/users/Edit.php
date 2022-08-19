<?php

$db = app()->db;
$tenant = app()->tenant;

$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ? AND Tenant = ?");
$userInfo->execute([
  $id,
  $tenant->getId()
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

// User object for permissions
$userObject = new \User($id);

function selected($userObject, $perm)
{
  if ($userObject->hasPermission($perm)) {
    return " checked ";
  } else {
    return "";
  }
}

$pagetitle = htmlspecialchars("Edit " . \SCDS\Formatting\Names::format($info['Forename'], $info['Surname']));

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">

  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users")) ?>">Users</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users/" . $id)) ?>"><?= htmlspecialchars(mb_substr($info["Forename"], 0, 1, 'utf-8') . mb_substr($info["Surname"], 0, 1, 'utf-8')) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">
          Edit
        </li>
      </ol>
    </nav>

    <h1>Edit <?= htmlspecialchars($info['Forename']) ?></h1>
    <p class="lead mb-0">Edit personal details, membership options and more</p>
  </div>

</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Success']) { ?>
        <div class="alert alert-success">
          <p class="mb-0">User details updated.</p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Success']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['InvalidEmail']) && $_SESSION['TENANT-' . app()->tenant->getId()]['InvalidEmail']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">The email you entered is invalid.</p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['InvalidEmail']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UsedEmail']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UsedEmail']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">The email you entered is already in use by another account.</p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UsedEmail']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['InvalidPhone']) && $_SESSION['TENANT-' . app()->tenant->getId()]['InvalidPhone']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">The phone number you entered is invalid.</p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['InvalidPhone']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['GeneralError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['GeneralError']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">An unspecified error occurred. Please try again.</p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['GeneralError']);
      } ?>

      <form method="post" id="main-form" class="needs-validation" novalidate>
        <h2>Personal details</h2>

        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="first-name">First name</label>
              <input type="text" class="form-control" id="first-name" name="first-name" placeholder="First" value="<?= htmlspecialchars($info['Forename']) ?>" required>
              <div class="invalid-feedback">
                Please provide a first name.
              </div>
            </div>
          </div>
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="last-name">Last name</label>
              <input type="text" class="form-control" id="last-name" name="last-name" placeholder="Last" value="<?= htmlspecialchars($info['Surname']) ?>" required>
              <div class="invalid-feedback">
                Please provide a surname name.
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="email-address">Email address</label>
          <input type="email" class="form-control" id="email-address" name="email-address" value="<?= htmlspecialchars($info['EmailAddress']) ?>" required data-valid="true" data-ajax-url="<?= htmlspecialchars(autoUrl("users/" . $id . "/edit/email")) ?>">
          <div class="invalid-feedback" id="email-error-message">
            Please provide a valid email address.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="mobile-phone">Mobile</label>
          <input type="tel" class="form-control" id="mobile-phone" name="mobile-phone" value="<?= htmlspecialchars($info['Mobile']) ?>" aria-describedby="phone-help" required>
          <small id="phone-help" class="form-text text-muted">You must use a UK phone number.</small>
          <div class="invalid-feedback">
            Please provide a mobile number.
          </div>
        </div>

        <h2>Access permissions</h2>

        <div class="mb-3">
          <p>
            Select access permissions for <?= htmlspecialchars($info['Forename']) ?>
          </p>
          <div class="form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="permissions-parent" name="permissions-parent" value="1" <?= selected($userObject, 'Parent') ?>>
            <label class="form-check-label" for="permissions-parent">Parent/user</label>
          </div>
          <div class="form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="permissions-gala" name="permissions-gala" value="1" <?= selected($userObject, 'Galas') ?>>
            <label class="form-check-label" for="permissions-gala">Gala coordinator</label>
          </div>
          <div class="form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="permissions-coach" name="permissions-coach" value="1" <?= selected($userObject, 'Coach') ?>>
            <label class="form-check-label" for="permissions-coach">Coach</label>
          </div>
          <div class="form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="permissions-admin" name="permissions-admin" value="1" <?= selected($userObject, 'Admin') ?>>
            <label class="form-check-label" for="permissions-admin">Admin</label>
          </div>
          <div class="form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="permissions-scds-payments-manager" name="permissions-scds-payments-manager" value="1" <?= selected($userObject, 'SCDSPaymentsManager') ?>>
            <label class="form-check-label" for="permissions-scds-payments-manager">SCDS Payments Access (Enabling this will also make the user an admin and allows them to manage payments to SCDS)</label>
          </div>
        </div>

        <p>Users can switch access level by selecting their name in the main menu.</p>

        <p>
          <button type="submit" class="btn btn-success">
            Save changes
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/users/Edit.js");
$footer->render();
