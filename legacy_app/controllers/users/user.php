<?php

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$db = app()->db;
$tenant = app()->tenant;

$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $id
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$logins = $db->prepare("SELECT `Time`, `IPAddress`, Browser, `Platform`, `GeoLocation` FROM userLogins WHERE UserID = ? ORDER BY `Time` DESC LIMIT 1");
$logins->execute([$id]);
$loginInfo = $logins->fetch(PDO::FETCH_ASSOC);

$userObj = new \User($id);

$par = $coa = $com = $gal = $adm = "";

$swimmers = null;
if ($userObj->hasPermission('Parent')) {
  $swimmers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn FROM members WHERE members.UserID = ?");
  $swimmers->execute([$id]);
}

$getSquads = $db->prepare("SELECT SquadName squad, SquadFee fee, Paying pays FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE Member = ?");

// Get Stripe direct debit info
$getStripeDD = $db->prepare("SELECT stripeMandates.ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates INNER JOIN stripeCustomers ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC LIMIT 1;");
if (stripeDirectDebit()) {
  $getStripeDD->execute([
    $id
  ]);
}
$stripeDD = $getStripeDD->fetch(PDO::FETCH_ASSOC);

$bankName = $bank = $has_logo = $logo_path = null;
if (userHasMandates($id)) {
  $bankName = mb_strtoupper(bankDetails($id, "account_holder_name"));
  if ($bankName != "UNKNOWN") {
    $bankName = $bankName . ', ';
  } else {
    $bankName = null;
  }
  $bank = mb_strtoupper(bankDetails($id, "bank_name"));
  $logo_path = getBankLogo($bank);
}

$json = $userObj->getUserOption('MAIN_ADDRESS');
$address = null;
if ($json != null) {
  $address = json_decode($json);
}

$number = null;
try {
  $number = PhoneNumber::parse($info['Mobile']);
} catch (PhoneNumberParseException $e) {
  $number = false;
}

$accessLevel = "";
$perms = $userObj->getPrintPermissions();
$firstDone = false;
foreach ($perms as $key => $value) {
  if ($firstDone) {
    $accessLevel .= ', ';
  }
  $accessLevel .= $value;
  $firstDone = true;
}

$pageHead = [
  'body' => [
    'data-bs-spy="scroll"',
    'data-bs-target="#member-page-menu"'
  ]
];

$fluidContainer = true;

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) . " Information";
$title = null;
include BASE_PATH . "views/header.php";
?>

<div class="bg-light mt-n3 py-3 mb-3">

  <div class="container-fluid">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("users") ?>">Users</a></li>
        <li class="breadcrumb-item active" aria-current="page">
          <?= htmlspecialchars(mb_substr($info["Forename"], 0, 1, 'utf-8') . mb_substr($info["Surname"], 0, 1, 'utf-8')) ?>
        </li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-sm-9 col-md-10 col-lg-11">
        <h1 class="mb-0">
          <?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?>
          <small><?= htmlspecialchars($accessLevel) ?></small>
        </h1>
        <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
          <div class="mb-3 d-md-none"></div>
        <?php } ?>
      </div>
      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <div class="col text-sm-end">
          <p class="mb-0">
            <a href="<?= htmlspecialchars(autoUrl("users/" . $id . "/edit")) ?>" class="btn btn-success">
              Edit
            </a>
          </p>
        </div>
      <?php } ?>
    </div>

  </div>

</div>

<div class="container-fluid">

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Error']) && $_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Error']) { ?>
    <div class="alert alert-danger">
      <strong>We were not able to update the user's email address because it was not valid</strong>
    </div>
  <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Error']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Success']) { ?>
    <div class="alert alert-success">
      <strong>We've updated the user's email address</strong>
    </div>
  <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Success']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivSuccess']) { ?>
    <div class="alert alert-success">
      <strong>We've sent your email to <?= htmlspecialchars($info['Forename']) ?></strong>
    </div>
  <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivSuccess']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationSuccess']) { ?>
    <div class="alert alert-success">
      <p class="mb-0">
        <strong>We've successfully created this user.</strong>
      </p>
      <p class="mb-0">
        This user will be log in using the password you have created or by following the self-service password reset process.
      </p>
    </div>
  <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['UserCreationSuccess']);
  } ?>

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
          <?php if ($userObj->hasPermission('Parent') && bool($info['RR'])) { ?>
            <a href="#user-registration" class="list-group-item list-group-item-action">
              User registration
            </a>
          <?php } ?>
          <?php if ($address != null) { ?>
            <a href="#residential-address" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Residential address
            </a>
          <?php } ?>
          <?php if ($userObj->hasPermission('Coach') && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
            <a href="#squads" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Squads
            </a>
          <?php } ?>
          <?php if ($userObj->hasPermission('Parent')) { ?>
            <a href="#payment-information" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Payment information
            </a>
            <a href="#members" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Members
            </a>
            <a href="#memberships" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Current Memberships
            </a>
          <?php } ?>
          <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
            <a href="#squad-rep-settings" class="list-group-item list-group-item-action">
              Squad rep settings
            </a>
            <a href="#targeted-list-settings" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Targeted list settings
            </a>
            <a href="#team-manager-settings" class="list-group-item list-group-item-action">
              Team manager settings
            </a>
          <?php } ?>
          <a href="#simulate-user" class="list-group-item list-group-item-action">
            Simulate user
          </a>
          <a href="#advanced-information" class="list-group-item list-group-item-action">
            Advanced information
          </a>
        </div>
      </div>
    </div>

    <div class="col">
      <div class="mb-4">
        <h2 id="basic-information">
          Basic Information
        </h2>
        <p class="lead">
          Basic contact details.
        </p>

        <div class="row">
          <div class="col-sm-6 col-lg-4">
            <h3 class="h6">Name</h3>
            <p><?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?></p>
          </div>
          <div class="col-sm-6 col-lg-4">
            <h3 class="h6">Email</h3>
            <p class="text-truncate"><a href="<?= htmlspecialchars(autoUrl("users/" . $id . "/email")) ?>"><?= htmlspecialchars($info['EmailAddress']) ?></a>
            </p>
          </div>
          <?php if ($number !== false) { ?>
            <div class="col-sm-6 col-lg-4">
              <h3 class="h6">Phone</h3>
              <p><a href="<?= htmlspecialchars($number->format(PhoneNumberFormat::RFC3966)) ?>"><?= htmlspecialchars($number->format(PhoneNumberFormat::INTERNATIONAL)) ?></a>
              </p>
            </div>
          <?php } ?>

        </div>
      </div>

      <hr>

      <?php if ($userObj->hasPermission('Parent') && bool($info['RR'])) { ?>
        <div class="mb-4">
          <h2 id="user-registration">
            User registration
          </h2>
          <p class="lead">Registration is still pending for this user.</p>
          <p>
            <button id="registration-resend-button" class="btn btn-primary" data-ajax-url="<?= htmlspecialchars(autoUrl("users/ajax/resend-registration-email")) ?>" data-user-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?>" data-user-edit-link="<?= htmlspecialchars(autoUrl("users/" . $id . "/edit")) ?>" data-user="<?= htmlspecialchars($id) ?>">
              Resend registration email <span class="fa fa-chevron-right"></span>
            </button>
          </p>
          <div id="resend-status"></div>
        </div>

        <hr>
      <?php } ?>

      <?php if ($address != null) { ?>
        <div class="mb-4">
          <h2 id="residential-address">
            Residential address
          </h2>
          <address>
            <?php if (isset($address->streetAndNumber)) { ?>
              <?= htmlspecialchars($address->streetAndNumber) ?><br>
            <?php } ?>
            <?php if (isset($address->flatOrBuilding)) { ?>
              <?= htmlspecialchars($address->flatOrBuilding) ?><br>
            <?php } ?>
            <?php if (isset($address->city)) { ?>
              <?= htmlspecialchars(mb_strtoupper($address->city)) ?><br>
            <?php } ?>
            <?php if (isset($address->county)) { ?>
              <?= htmlspecialchars($address->county) ?><br>
            <?php } ?>
            <?php if (isset($address->postCode)) { ?>
              <?= htmlspecialchars(mb_strtoupper($address->postCode)) ?>
            <?php } ?>
          </address>
        </div>

        <hr>
      <?php } ?>

      <?php if ($userObj->hasPermission('Coach') && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <div class="mb-4" id="coach-squad" data-squad-list="<?= htmlspecialchars(json_encode(false)) ?>">
          <h2 id="squads">
            Squads
          </h2>
          <p class="lead">
            Assign <?= htmlspecialchars($info['Forename']) ?> as a coach for a squad.
          </p>

          <div id="coach-squad-list" data-user-id="<?= htmlspecialchars($id) ?>" data-ajax-url="<?= htmlspecialchars(autoUrl("users/squads/list")) ?>"></div>

          <p id="coach-squad-assign-container" class="d-none">
            <button id="coach-squad-assign" class="btn btn-primary" data-user-id="<?= htmlspecialchars($id) ?>" data-ajax-url="<?= htmlspecialchars(autoUrl("users/squads/assign-delete")) ?>">
              Assign a squad
            </button>
          </p>
        </div>

        <hr>
      <?php } ?>

      <?php if ($userObj->hasPermission('Parent')) { ?>
        <div class="mb-4">
          <div class="row">
            <div class="col-md-6 col-lg-8">
              <h2 id="payment-information">
                Payment information
              </h2>
              <p class="lead">
                Account details and monthly fees paid by this user.
              </p>

              <div class="card card-body mb-3">

                <h3 class="mb-3">
                  Payment Information
                </h3>

                <div class="row">
                  <div class="col-lg-6">
                    <h3 class="h6">Squad Fees</h3>
                    <p><?= monthlyFeeCost($db, $id, "string") ?></p>
                  </div>
                  <div class="col-lg-6">
                    <h3 class="h6">Extra Fees</h3>
                    <p><?= monthlyExtraCost($db, $id, "string") ?></p>
                  </div>
                  <div class="col-lg-6">
                    <h3 class="h6">Account balance</h3>
                    <p class="mb-0">
                      &pound;<?= (string) (\Brick\Math\BigDecimal::of((string) getAccountBalance($id)))->withPointMovedLeft(2)->toScale(2) ?>
                    </p>
                  </div>
                </div>
              </div>

              <?php if ($tenant->getKey('GOCARDLESS_ACCESS_TOKEN') && !$tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) { ?>
                <div class="alert alert-info">
                  <p class="mb-0">
                    <strong>Plan your migration to Stripe for your Direct Debit payments</strong>
                  </p>
                  <p class="mb-0">
                    The GoCardless service is deprecated and will eventually be turned off. To start planning your migration to the Stripe powered service, please talk to SCDS for help and support.
                  </p>
                </div>
              <?php } ?>

              <div class="card card-body">
                <h3 class="mb-3">
                  Current Direct Debit Mandates
                </h3>

                <div class="row">
                  <div class="col-lg">
                    <h4>Stripe DD (New System)</h4>
                    <?php if ($stripeDD) { ?>
                      <p class="mb-0"><strong>Sort Code</strong> <span class="font-monospace"><?= htmlspecialchars(implode("-", str_split($stripeDD['SortCode'], 2))) ?></span>
                      </p>
                      <p class="mb-0"><strong>Account Number</strong> <span class="font-monospace">&middot;&middot;&middot;&middot;<?= htmlspecialchars($stripeDD['Last4']) ?></span></p>
                    <?php } else { ?>
                      <p class="mb-0">No Direct Debit set up</p>
                    <?php } ?>
                    <div class="mb-3 d-lg-none"></div>
                  </div>

                  <div class="col-lg">
                    <h4>GoCardless DD (Legacy System)</h4>
                    <?php if (userHasMandates($id)) { ?>
                      <?php if ($logo_path) { ?>
                        <img class="img-fluid mb-3" style="max-height:35px;" src="<?= $logo_path ?>.png" srcset="<?= $logo_path ?>@2x.png 2x, <?= $logo_path ?>@3x.png 3x">
                      <?php } ?>
                      <p class="mb-0"><?= $bankName ?><abbr title="<?= htmlspecialchars(mb_strtoupper(bankDetails($id, "bank_name"))) ?>"><?= htmlspecialchars(getBankName(bankDetails($id, "bank_name"))) ?></abbr>
                      </p>
                      <p class="mb-0 font-monospace">&middot;&middot;&middot;&middot;&middot;&middot;<?= mb_strtoupper(bankDetails($id, "account_number_end")) ?></p>

                    <?php } else { ?>
                      <p class="mb-0">No Direct Debit set up</p>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <div class="mb-3 d-md-none"></div>

            </div>

            <div class="col-md-6 col-lg-4">
              <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
                <div class="card position-sticky top-3">
                  <div class="card-header">
                    Payment links
                  </div>
                  <div class="list-group list-group-flush">
                    <a href="<?= htmlspecialchars(autoUrl("users/" . $id . "/membership-fees")) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">Annual membership fees <span class="fa fa-chevron-right"></span></a>
                    <a href="<?= autoUrl("users/" . $id . "/pending-fees") ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">Pending payments <span class="fa fa-chevron-right"></span></a>
                    <a href="<?= autoUrl("payments/history/users/" . $id) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">Previous bills <span class="fa fa-chevron-right"></span></a>
                    <?php if ($tenant->getKey('GOCARDLESS_ACCESS_TOKEN')) { ?>
                      <a href="<?= autoUrl("users/" . $id . "/mandates") ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">GoCardless direct debit mandates <span class="fa fa-chevron-right"></span></a>
                    <?php } ?>
                    <?php if (stripeSetUpDirectDebit()) { ?>
                      <a href="<?= autoUrl("users/" . $id . "/direct-debit") ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">Stripe direct debit mandates <span class="fa fa-chevron-right"></span></a>
                    <?php } ?>
                    <?php if ($stripeDD || userHasMandates($id)) { ?>
                      <button id="trigger-early-payment" data-info-url="<?= htmlspecialchars(autoUrl("users/$id/direct-debit/force-run-info")) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">Trigger early payment <span class="fa fa-chevron-right"></span></button>
                    <?php } ?>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>

        <hr>

        <div class="mb-4">
          <h2 id="members">
            Members
          </h2>
          <p class="lead">
            Members linked to this account
          </p>

          <?php
          $s = $swimmers->fetch(PDO::FETCH_ASSOC);
          if ($s) { ?>
            <div class="row">
              <?php do { ?>
                <div class="col-sm-6 col-lg-4">
                  <h3 class="h6"><a href="<?= autoUrl("swimmers/" . $s['id']) ?>" title="Full information about <?= htmlspecialchars(\SCDS\Formatting\Names::format($s['fn'], $s['sn'])) ?>"><?= htmlspecialchars(\SCDS\Formatting\Names::format($s['fn'], $s['sn'])) ?></a>
                  </h3>
                  <?php
                  $getSquads->execute([
                    $s['id']
                  ]); ?>
                  <ul class="mb-0 list-unstyled">
                    <?php if ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
                      do { ?>
                        <li><?= htmlspecialchars($squad['squad']) ?>, <em><?php if (!bool($squad['pays']) || (int) $squad['fee'] == 0) { ?><?php } else { ?>&pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $squad['fee']))->toScale(2) ?>/month<?php } ?></em></li>
                      <?php } while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC));
                    } else { ?>
                      <li>No squads</li>
                    <?php } ?>
                  </ul>
                  <div class="mb-3"></div>
                </div>
              <?php } while ($s = $swimmers->fetch(PDO::FETCH_ASSOC)); ?>
            </div>
          <?php } else { ?>
            <div class="alert alert-warning mb-0">
              <p class="mb-0">
                <strong>There are no members linked to <?= htmlspecialchars($info['Forename']) ?>'s account</strong>
              </p>
              <p class="mb-0">
                Members can be added using <a href="<?= htmlspecialchars(autoUrl("assisted-registration")) ?>" class="alert-link">assissted registration</a>.
              </p>
            </div>
          <?php } ?>
        </div>

        <hr>

        <div class="mb-4">

          <h2 id="memberships">Current Memberships</h2>

          <p class="lead">
            We're adding new features which will change the way the membership system tracks which memberships a member holds.
          </p>

          <p class="">
            <a href="<?= htmlspecialchars(autoUrl("users/$id/new-membership-batch")) ?>" class="btn btn-primary">New Payment Batch</a>
          </p>

          <p class="">
            <a href="<?= htmlspecialchars(autoUrl("users/$id/current-memberships")) ?>" class="btn btn-primary">View Current Memberships</a> <i>including totals paid for this year</i>
          </p>

          <p class="">
            <a href="<?= htmlspecialchars(autoUrl("onboarding/new?user=$id")) ?>" class="btn btn-primary">Repeat onboarding forms</a>
          </p>

          <p class="">
            <a href="<?= htmlspecialchars(autoUrl("onboarding/view-sessions?user=$id")) ?>" class="btn btn-primary">View current onboarding sessions</a>
          </p>

        </div>

        <hr>

      <?php } ?>

      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <div class="row">
          <div class="col-12">
            <div class="mb-4">
              <h2 id="squad-rep-settings">
                Squad rep settings
              </h2>
              <p class="lead">
                Make this user a squad rep.
              </p>

              <p>
                <a href="<?= autoUrl("users/" . $id . "/rep") ?>" class="btn btn-primary">
                  Rep settings <span class="fa fa-chevron-right"></span>
                </a>
              </p>
            </div>
            <hr>
          </div>

          <div class="col-12">
            <div class="mb-4">
              <h2 id="targeted-list-settings">
                Targeted list settings
              </h2>
              <p class="lead">
                Grant a user permission to use a notify targeted list
              </p>

              <p>
                <a href="<?= autoUrl("users/" . $id . "/targeted-lists") ?>" class="btn btn-primary">
                  Notify lists <span class="fa fa-chevron-right"></span>
                </a>
              </p>
            </div>
            <hr>
          </div>

          <div class="col-12">
            <div class="mb-4">
              <h2 id="team-manager-settings">
                Team manager settings
              </h2>
              <p class="lead">
                Make this parent a team manager for a specific gala.
              </p>

              <p>
                <a href="<?= autoUrl("users/" . $id . "/team-manager") ?>" class="btn btn-primary">
                  Team manager settings <span class="fa fa-chevron-right"></span>
                </a>
              </p>
            </div>
            <hr>
          </div>
        </div>
      <?php } ?>

      <div class="mb-4">
        <h2 id="simulate-user">Simulate this user</h2>
        <p class="lead">
          Act as this user.
        </p>

        <p>
          You can use this feature to provide help and support to other users. It
          will be as if you have logged in as this user.
        </p>

        <p>
          <strong>Usage of this feature is recorded.</strong> Any actions will still be recorded as being performed by yourself in audit logs.
        </p>

        <p><a href="<?= autoUrl("users/simulate/" . $id) ?>" class="btn btn-primary">Simulate this user <span class="fa fa-chevron-right"></span> </a></p>
      </div>

      <hr>

      <h2 id="advanced-information">
        Advanced Information
      </h2>
      <p class="lead">
        For troubleshooting.
      </p>

      <div class="mb-4">
        <div class="row">
          <div class="col-sm-6 col-lg-4">
            <h3 class="h6">Last Login</h3>
            <?php
            $details = "";
            if ($loginInfo == null) {
              // User has never logged in
              $details = "This user has never logged in";
            } else {
              $time = new DateTime($loginInfo['Time'], new DateTimeZone('UTC'));
              $time->setTimezone(new DateTimeZone('Europe/London'));
              $details = $time->format('H:i T \o\n j F Y') . " from " . htmlspecialchars($loginInfo['Browser']) . " on " . htmlspecialchars($loginInfo['Platform']) . " (" . htmlspecialchars($loginInfo['IPAddress']) . ")";
            } ?>
            <p><?= $details ?></p>
          </div>

          <div class="col-sm-6 col-lg-4">
            <h3 class="h6">Delete account</h3>
            <p>
              <button data-ajax-url="<?= htmlspecialchars(autoUrl("users/delete-user")) ?>" data-users-url="<?= htmlspecialchars(autoUrl("users")) ?>" data-user-id="<?= htmlspecialchars($id) ?>" data-user-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?>" id="delete-button" class="btn btn-danger">
                Delete account
              </button>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for use by JS code -->
<div class="modal fade" id="main-modal" tabindex="-1" role="dialog" aria-labelledby="main-modal-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="main-modal-title">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body" id="main-modal-body">
        ...
      </div>
      <div class="modal-footer" id="main-modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="modal-confirm-button" class="btn btn-success">Confirm</button>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->addJS("js/numerical/bignumber.min.js");
$footer->addJS("js/users/main.js");
$footer->useFluidContainer();
$footer->render();
