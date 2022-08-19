<?php

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$db = app()->db;

$pagetitle = "Users - Admin Dashboard - SCDS";

$logins = $db->prepare("SELECT `Time`, `IPAddress`, Browser, `Platform`, `GeoLocation` FROM userLogins WHERE UserID = ? ORDER BY `Time` DESC LIMIT 1");

$getUsers = $db->prepare("SELECT UserID, Forename, Surname, EmailAddress, Mobile, tenants.Name, tenants.ID, tenants.Code FROM users INNER JOIN tenants ON tenants.ID = users.Tenant WHERE users.EmailAddress = ? ORDER BY Forename ASC, Surname ASC");
$user = null;
if (isset($_GET['email-address'])) {
  $getUsers->execute([
    mb_strimwidth($_GET['email-address'], 0, 255),
  ]);
  $user = $getUsers->fetch(PDO::FETCH_ASSOC);
}

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item active" aria-current="page">Users</li>
    </ol>
  </nav>

  <h1>
    User Search
  </h1>
  <p class="lead">Search for a user by email.</p>

  <form method="get" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label" for="email-address">Email</label>
      <input type="email" id="email-address" name="email-address" class="form-control" placeholder="name@example.com" <?php if (isset($_GET['email-address'])) { ?>value="<?= htmlspecialchars($_GET['email-address']) ?>" <?php } ?> required>
      <div class="invalid-feedback">
        Please enter a valid email address
      </div>
    </div>

    <p>
      <button type="submit" class="btn btn-primary">
        Search
      </button>
    </p>
  </form>

  <?php if (isset($_GET['email-address']) && !$user) { ?>
    <hr>

    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>No users found</strong>
      </p>
      <p class="mb-0">
        Please try another search
      </p>
    </div>
  <?php } else if ($user) { ?>
    <hr>

    <ul class="list-group">
      <?php do {

        $number = null;
        try {
          $number = PhoneNumber::parse($user['Mobile']);
        } catch (PhoneNumberParseException $e) {
          $number = false;
        }

        $tenantUrl = $user['ID'];
        if ($user['Code']) {
          $tenantUrl = mb_strtolower($user['Code']);
        }

        $logins->execute([$user['UserID']]);
        $loginInfo = $logins->fetch(PDO::FETCH_ASSOC);

        $details = "";
        if ($loginInfo == null) {
          // User has never logged in
          $details = "This user has never logged in";
        } else {
          $time = new DateTime($loginInfo['Time'], new DateTimeZone('UTC'));
          $time->setTimezone(new DateTimeZone('Europe/London'));
          $details = $time->format('H:i T \o\n j F Y') . " from " . htmlspecialchars($loginInfo['Browser']) . " on " . htmlspecialchars($loginInfo['Platform']) . " (" . htmlspecialchars($loginInfo['IPAddress']) . ")";
        }

        ?>
        <li class="list-group-item">
          <h2><?= htmlspecialchars($user['Forename'] . ' ' . $user['Surname']) ?></h2>
          <p class="lead">
            <?= htmlspecialchars($user['Name']) ?>
          </p>

          <dl class="row">
            <dt class="col-md-3">
              Email
            </dt>
            <dd class="col-md-9">
              <?= htmlspecialchars($user['EmailAddress']) ?>
            </dd>

            <dt class="col-md-3">
              Phone
            </dt>
            <dd class="col-md-9">
              <?php if ($number) { ?><a href="<?= htmlspecialchars($number->format(PhoneNumberFormat::RFC3966)) ?>"><?= htmlspecialchars($number->format(PhoneNumberFormat::INTERNATIONAL)) ?></a><?php } else { ?><?= htmlspecialchars($user['Mobile']) ?><?php } ?>
            </dd>

            <dt class="col-md-3">
              Last Login
            </dt>
            <dd class="col-md-9">
              <?= $details ?>
            </dd>
          </dl>

          <p class="mb-0">
            <a target="_blank" href="<?= htmlspecialchars(autoUrl($tenantUrl . '/users/' . $user['UserID'])) ?>" class="btn btn-primary">View in tenant</a>
            <a target="_self" href="<?= htmlspecialchars(autoUrl('admin/audit/requests?user=' . $user['UserID'])) ?>" class="btn btn-primary">View HTTP requests</a>
          </p>
        </li>
      <?php } while ($user = $getUsers->fetch(PDO::FETCH_ASSOC)); ?>
    </ul>
  <?php } ?>

</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
