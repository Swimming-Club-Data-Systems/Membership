<?php

/**
 * User mandate list for membership system
 * Displays list of users and their primary mandate
 */

$db = app()->db;
$tenant = app()->tenant;

$getMandates = $db->prepare("SELECT Forename, Surname, users.UserID, Mandate, BankName, AccountHolderName, AccountNumEnd FROM (((users LEFT JOIN paymentPreferredMandate ON users.UserID = paymentPreferredMandate.UserID) LEFT JOIN paymentMandates ON paymentPreferredMandate.MandateID = paymentMandates.MandateID) INNER JOIN `permissions` ON users.UserID = `permissions`.`User`) WHERE users.Tenant = ? AND `permissions`.`Permission` = 'Parent' ORDER BY Surname ASC, Forename ASC");
$getMandates->execute([
  $tenant->getId()
]);
$mandate = $getMandates->fetch(PDO::FETCH_ASSOC);

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
      <p class="lead">(GoCardless <em>Legacy</em>) Direct Debit mandates by user</p>

      <?php if (app()->tenant->getStripeAccount() || app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP')) { ?>
        <p>
          <a href="<?= htmlspecialchars(autoUrl('payments/user-mandates')) ?>">View Stripe Mandates</a>
        </p>
      <?php } ?>
    </div>
  </div>

  <?php if ($mandate == null) { ?>
    <div class="alert alert-warning">
      <strong>There are no users to display</strong>
    </div>
  <?php } else { ?>
    <div class="list-group">
      <?php do { ?>
        <a href="<?= htmlspecialchars(autoUrl("users/" . $mandate['UserID'] . '#payment-information')) ?>" class="list-group-item list-group-item-action <?php if (!$mandate['BankName']) { ?> list-group-item-danger <?php } ?>">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h2 class="mb-0 h4"><?= htmlspecialchars(\SCDS\Formatting\Names::format($mandate['Surname'], $mandate['Forename'])) ?></h2>
              <div class="mb-3 d-md-none"></div>
            </div>
            <div class="col text-md-end">
              <?php if ($mandate['BankName']) { ?>
                <p class="font-monospace mb-0"><?= htmlspecialchars(getBankName($mandate['BankName'])) ?></p>
                <p class="font-monospace mb-0"><?= htmlspecialchars($mandate['AccountHolderName']) ?>, &#0149;&#0149;&#0149;&#0149;&#0149;&#0149;<?= htmlspecialchars($mandate['AccountNumEnd']) ?></p>
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
