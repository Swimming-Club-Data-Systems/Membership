<?php

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberType;
use Brick\PhoneNumber\PhoneNumberFormat;
// $countries = getISOAlpha2Countries();

function valueOrString($name)
{
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError']['fields'][$name])) {
    return $_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError']['fields'][$name];
  }
  return '';
}

function checked($name)
{
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError']['fields'][$name]) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError']['message'][$name])) {
    return 'checked';
  }
  return '';
}

$examplePhone = '+447400123456';
try {
  $examplePhone = PhoneNumber::getExampleNumber('GB', PhoneNumberType::MOBILE)->format(PhoneNumberFormat::E164);
} catch (Exception $e) {
}

$pagetitle = 'Create a new user';

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="<?= htmlspecialchars(autoUrl("users")) ?>">Users</a>
      </li>
      <li class="breadcrumb-item active" aria-current="page">
        Create
      </li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>
        Create a new user
      </h1>
      <p class="lead">
        We've removed the ability for users to create their own account. Instead, you can now provision users here.
      </p>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>We could not add the user</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError']['message']) ?>
          </p>
        </div>
      <?php } ?>

      <p>
        Members and volunteers can use a single account with multiple permissions assigned to them.
      </p>

      <p>
        If you're adding a new member/parent, please continue to use <a href="<?= htmlspecialchars(autoUrl('assisted-registration')) ?>">Assisted Registration</a>. If that new user requires admin or coaching access to the system, you can grant access once you have created their account with Assisted Registration.
      </p>

      <form method="post" class="needs-validation" novalidate>

        <h2>Basic details</h2>

        <div class="row">
          <div class="mb-3 col-md-6">
            <label class="form-label" for="first-name">First name</label>
            <input type="text" required class="form-control" name="first-name" id="first-name" placeholder="First" value="<?= htmlspecialchars(valueOrString('first-name')) ?>">
            <div class="invalid-feedback">
              Please provide a first name
            </div>
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label" for="last-name">Last name</label>
            <input type="text" required class="form-control" name="last-name" id="last-name" placeholder="Last" value="<?= htmlspecialchars(valueOrString('last-name')) ?>">
            <div class="invalid-feedback">
              Please provide a last name
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="email-address">Email address</label>
          <input type="email" required class="form-control" name="email-address" id="email-address" placeholder="first.last@example.com" value="<?= htmlspecialchars(valueOrString('email-address')) ?>">
          <div class="invalid-feedback">
            Please provide a valid email address
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="phone">Mobile number</label>
          <input type="tel" pattern="\+{0,1}[0-9]*" required class="form-control" name="phone" id="phone" placeholder="<?= htmlspecialchars($examplePhone) ?>" value="<?= htmlspecialchars(valueOrString('phone')) ?>">
          <div class="invalid-feedback">
            You must provide a valid UK phone number
          </div>
        </div>

        <div class="row" id="password-row">
          <div class="mb-3 col-md-6">
            <label class="form-label" for="password-1">Password</label>
            <input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required class="form-control" name="password-1" id="password-1" aria-describedby="pwHelp">
            <small id="pwHelp" class="form-text text-muted">Use 8 characters or more, with at least one lowercase letter, at least one uppercase letter and at least one number</small>
            <div class="invalid-feedback">
              You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
            </div>
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label" for="password-2">Confirm password</label>
            <input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required class="form-control" name="password-2" id="password-2">
            <div class="invalid-feedback">
              Passwords do not match
            </div>
          </div>
        </div>

        <div class="alert alert-info d-none" id="pwned-password-warning">
          <p class="mb-0">
            <strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Warning</strong>
          </p>
          <p class="mb-0">
            That password has been part of a data breach elsewhere on the internet. We suggest you pick something different.
          </p>
        </div>

        <h2>Access permissions</h2>

        <div class="mb-3">
          <p>
            Select access permissions for this user
          </p>
          <div class="form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="permissions-gala" name="permissions-gala" value="1" <?= checked('permissions-gala') ?>>
            <label class="form-check-label" for="permissions-gala">Gala coordinator</label>
          </div>
          <div class="form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="permissions-coach" name="permissions-coach" value="1" <?= checked('permissions-coach') ?>>
            <label class="form-check-label" for="permissions-coach">Coach</label>
          </div>
          <div class="form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="permissions-admin" name="permissions-admin" value="1" <?= checked('permissions-admin') ?>>
            <label class="form-check-label" for="permissions-admin">Admin</label>
          </div>
        </div>

        <p>Users can switch access level by selecting their name in the main menu.</p>

        <h2>What's next?</h2>

        <p>
          This user will be log in using the password you have created or by following the self-service password reset process.
        </p>

        <p>
          If it turns out there were mistakes in this form, you can edit the user's details via the <strong>Users</strong> section of the membership system.
        </p>

        <?= SCDS\CSRF::write() ?>

        <p>
          <button type="submit" class="btn btn-primary">
            Create user
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<div id="ajax-options" data-get-pwned-list-ajax-url="<?= htmlspecialchars(autoUrl('ajax-utilities/pwned-password-check')) ?>" data-cross-site-request-forgery-value="<?= htmlspecialchars(\SCDS\CSRF::getValue()) ?>"></div>

<?php

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError'])) unset($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationError']);

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->addJS("js/ajax-utilities/pwned-password-check.js");
$footer->render();
