<?php

$db = app()->db;
$tenant = app()->tenant;

$userName = $db->prepare("SELECT Forename, Surname FROM users WHERE UserID = ? AND Tenant = ?");
$userName->execute([
  $user,
  $tenant->getId()
]);
$un = $userName->fetch(PDO::FETCH_ASSOC);

if (!$un) {
  halt(404);
}

// Get mandates
$getMandates = $db->prepare("SELECT ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates WHERE Customer = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC LIMIT 1");
$getMandates->execute([
  app()->user->getStripeCustomer()->id,
]);
$mandate = $getMandates->fetch(PDO::FETCH_ASSOC);

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($un['Forename'], $un['Surname']) . "'s Direct Debit Mandate");

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users")) ?>">Users</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users/" . $user)) ?>"><?= htmlspecialchars(mb_substr($un["Forename"], 0, 1, 'utf-8') . mb_substr($un["Surname"], 0, 1, 'utf-8')) ?></a></li>
      <li class="breadcrumb-item active" aria-current="page">Bank</li>
    </ol>
  </nav>

  <h1>Direct Debit Mandates (Stripe)</h1>
  <p class="lead">
    <?= htmlspecialchars($un['Forename']) ?>'s mandates
  </p>

  <p>
    You can view the status of a mandate
    <!--or cancel it -->from this page.
  </p>
  <!-- <p>
    <strong>Beware:</strong> Mandate cancellations are instant and cannot be reversed.
  </p> -->

  <?php if ($mandate) { ?>
    <?php do { ?>
      <div class="card card-body mb-3">
        <h2><span class="font-monospace"><?= htmlspecialchars($mandate['ID']) ?></span> <span class="badge bg-secondary"><?php if ($mandate['Status'] == 'pending') { ?>Pending<?php } else if ($mandate['Status'] == 'accepted') { ?>Active<?php } ?></span></h2>
        <dl class="row">
          <dt class="col-sm-3">Sort code</dt>
          <dd class="col-sm-9 font-monospace"><?= htmlspecialchars(implode("-", str_split($mandate['SortCode'], 2))) ?></dd>

          <dt class="col-sm-3">Account number</dt>
          <dd class="col-sm-9 font-monospace">&middot;&middot;&middot;&middot;<?= htmlspecialchars($mandate['Last4']) ?></dd>

          <dt class="col-sm-3">Payment reference</dt>
          <dd class="col-sm-9 font-monospace"><?= htmlspecialchars($mandate['Reference']) ?></dd>
        </dl>

        <p class="mb-0">
          <a href="<?= htmlspecialchars($mandate['URL']) ?>" target="_blank">
            View Direct Debit Instruction
          </a>
        </p>
      </div>
    <?php } while ($mandate = $getMandates->fetch(PDO::FETCH_ASSOC)); ?>
  <?php } else { ?>
    <div class="alert alert-warning">
      <strong><?= htmlspecialchars($un['Forename']) ?> does not have a direct debit set up</strong> <br>
      Ask them to set one up in their club account if they need to pay by Direct Debit.
    </div>
  <?php } ?>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
