<?php

/**
 * User mandate list for membership system
 * Displays list of users and their primary mandate
 */

if (!app()->tenant->getStripeAccount() || !app()->tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) {
  http_response_code(302);
  header('location: ' . autoUrl('payments/user-mandates/go-cardless'));
  return;
}

$db = app()->db;
$tenant = app()->tenant;

$getMandates = $db->prepare("SELECT Forename, Surname, users.UserID FROM (users INNER JOIN `permissions` ON users.UserID = `permissions`.`User`) WHERE users.Tenant = ? AND `permissions`.`Permission` = 'Parent' ORDER BY Surname ASC, Forename ASC");
$getMandates->execute([
  $tenant->getId()
]);
$mandate = $getMandates->fetch(PDO::FETCH_ASSOC);

// Get Stripe direct debit info
$getStripeDD = $db->prepare("SELECT stripeMandates.ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates INNER JOIN stripeCustomers ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC LIMIT 1;");

$pagetitle = 'User Mandates';
include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments')) ?>">Payments</a></li>
      <li class="breadcrumb-item active" aria-current="page">Mandates</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>User mandates</h1>
      <p class="lead">(Stripe) Direct Debit mandates by user</p>

      <p>
        <a href="<?= htmlspecialchars(autoUrl('payments/user-mandates/go-cardless')) ?>">View GoCardless Mandates</a>
      </p>
    </div>
  </div>

  <?php if ($mandate == null) { ?>
    <div class="alert alert-warning">
      <strong>There are no users to display</strong>
    </div>
  <?php } else { ?>
    <div class="list-group">
      <?php do {

        $getStripeDD->execute([
          $mandate['UserID']
        ]);
        $stripeDD = $getStripeDD->fetch(PDO::FETCH_ASSOC);

      ?>
        <a href="<?= htmlspecialchars(autoUrl("users/" . $mandate['UserID'] . '#payment-information')) ?>" class="list-group-item list-group-item-action <?php if (!$stripeDD) { ?> list-group-item-danger <?php } ?>">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h2 class="mb-0 h4"><?= htmlspecialchars(\SCDS\Formatting\Names::format($mandate['Surname'], $mandate['Forename'])) ?></h2>
              <div class="mb-3 d-md-none"></div>
            </div>
            <div class="col text-md-end">
              <?php if ($stripeDD) { ?>
                <p class="font-monospace mb-0"><strong>Sort Code</strong> <span class="font-monospace"><?= htmlspecialchars(implode("-", str_split($stripeDD['SortCode'], 2))) ?></span>
                </p>
                <p class="font-monospace mb-0"><strong>Account Number</strong> <span class="font-monospace">&middot;&middot;&middot;&middot;<?= htmlspecialchars($stripeDD['Last4']) ?></span></p>
              <?php } else { ?>
                <p class="font-monospace mb-0">No mandate</p>
                <p class="font-monospace mb-0">Contact user</p>
              <?php } ?>
            </div>
          </div>
        </a>
      <?php } while ($mandate = $getMandates->fetch(PDO::FETCH_ASSOC)); ?>
    </div>
  <?php } ?>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
