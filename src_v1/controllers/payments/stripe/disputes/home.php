<?php

if (!app()->user->hasPermission('Admin')) {
  halt(404);
}

if (!app()->tenant->getStripeAccount()) {
  halt(404);
}

$db = app()->db;
$tenant = app()->tenant;

$start = $page = 0;

if (isset($_GET['page']) && ((int) $_GET['page']) != 0) {
  $page = (int) $_GET['page'];
  $start = ($page - 1) * 10;
} else {
  $page = 1;
}

$getDisputeCount = $db->prepare("SELECT COUNT(*) FROM stripeDisputes WHERE Tenant = ?");
$getDisputeCount->execute([
  $tenant->getId(),
]);
$count = $getDisputeCount->fetchColumn();

if ($start > $count) {
  halt(404);
}

$getDisputes = $db->prepare("SELECT `ID`, `SID`, `Amount`, `Currency`, `PaymentIntent`, `Reason`, `Status`, `Created`, `EvidenceDueBy`, `IsRefundable`, `HasEvidence`, `EvidencePastDue`, `EvidenceSubmissionCount` FROM stripeDisputes WHERE Tenant = :tenant ORDER BY Created DESC LIMIT :offset, :num");
$getDisputes->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
$getDisputes->bindValue(':offset', $start, PDO::PARAM_INT);
$getDisputes->bindValue(':num', 10, PDO::PARAM_INT);
$getDisputes->execute();

$pagetitle = "Disputes - Page " . htmlspecialchars($page);
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('payments')) ?>">Payments</a></li>
        <li class="breadcrumb-item active" aria-current="page">Disputes</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Payment Disputes
        </h1>
        <p class="lead mb-0">
          Dispute information for card and direct debit payments
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <?php if ($dispute = $getDisputes->fetch(PDO::FETCH_ASSOC)) { ?>
        <ul class="list-group">
          <?php do {

            $created = $evidenceDue = null;
            if ($dispute['Created']) {
              try {
                $created = new DateTime($dispute['Created'], new DateTimeZone('UTC'));
                $created->setTimezone(new DateTimeZone('Europe/London'));
              } catch (Exception $e) {
                // Ignore
              }
            }

            if ($dispute['EvidenceDueBy']) {
              try {
                $evidenceDue = new DateTime($dispute['EvidenceDueBy'], new DateTimeZone('UTC'));
                $evidenceDue->setTimezone(new DateTimeZone('Europe/London'));
              } catch (Exception $e) {
                // Ignore
              }
            }

          ?>

            <li class="list-group-item" id="<?= htmlspecialchars('containing-box:' . $dispute['ID']) ?>">

              <h2><?= htmlspecialchars($dispute['SID']) ?></h2>
              <p class="lead">
                <?php if ($created) { ?>Created at <?= htmlspecialchars($created->format("H:i, j F Y")) ?><?php } else { ?>No creation time available<?php } ?>
              </p>

              <p>
                Status: <span class="font-monospace bg-light p-1"><?= htmlspecialchars($dispute['Status']) ?></span>
              </p>

              <p>
                Reason: <span class="font-monospace bg-light p-1"><?= htmlspecialchars($dispute['Reason']) ?></span>
              </p>

              <?php if (!bool($dispute['EvidencePastDue']) && $evidenceDue) { ?>
                <p>
                  Evidence to refute the customers claims is due by <?= htmlspecialchars($evidenceDue->format("H:i, j F Y")) ?>.
                </p>
              <?php } else if (bool($dispute['EvidencePastDue']) && $evidenceDue) { ?>
                <p>
                  The evidence due date (<?= htmlspecialchars($evidenceDue->format("H:i, j F Y")) ?>) has passed.
                </p>
              <?php } else { ?>
                <p>
                  You can't provide any evidence for this dispute. This is the case when disputes are final.
                </p>
              <?php } ?>

            </li>

          <?php } while ($dispute = $getDisputes->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
      <?php } else { ?>
        <div class="alert alert-info">
          <p class="mb-0">
            <strong>Great news!</strong>
          </p>
          <p class="mb-0">
            You've never had any disputed payments. Keep up the good work by ensuring you remove leaving members from squad lists before the next billing cycle.
          </p>
        </div>
      <?php } ?>


      <nav aria-label="Page navigation">
        <ul class="pagination">
          <?php if ($count <= 10) { ?>
            <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page ?>"><?php echo $page ?></a></li>
          <?php } else if ($count <= 20) { ?>
            <?php if ($page == 1) { ?>
              <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page ?>"><?php echo $page ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page + 1 ?>">Next</a></li>
            <?php } else { ?>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page - 1 ?>">Previous</a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page - 1 ?>"><?php echo $page - 1 ?></a></li>
              <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page ?>"><?php echo $page ?></a></li>
            <?php } ?>
          <?php } else { ?>
            <?php if ($page == 1) { ?>
              <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page ?>"><?php echo $page ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page + 2 ?>"><?php echo $page + 2 ?></a></li>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page + 1 ?>">Next</a></li>
            <?php } else { ?>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page - 1 ?>">Previous</a></li>
              <?php if ($page > 2) { ?>
                <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page - 2 ?>"><?php echo $page - 2 ?></a></li>
              <?php } ?>
              <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page - 1 ?>"><?php echo $page - 1 ?></a></li>
              <li class="page-item active"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page ?>"><?php echo $page ?></a></li>
              <?php if ($count > $page * 10) { ?>
                <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
                <?php if ($count > $page * 10 + 10) { ?>
                  <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page + 2 ?>"><?php echo $page + 2 ?></a></li>
                <?php } ?>
                <li class="page-item"><a class="page-link" href="<?= htmlspecialchars(autoUrl("payments/disputes?page=")) ?><?php echo $page + 1 ?>">Next</a></li>
              <?php } ?>
            <?php } ?>
          <?php } ?>
        </ul>
      </nav>
    </div>

    <div class="col">
      <div class="cell">
        <p>
          <a href="https://dashboard.stripe.com/disputes" class="btn btn-primary" target="_blank">View in Stripe Dashboard <i class="fa fa-external-link" aria-hidden="true"></i></a>
        </p>

        <p>
          Disputes include retrievals/inquiries and chargebacks. When customers chargeback payments, their bank will refund their account and the money plus a dispute fee will be withdrawn from your Stripe account.
        </p>

        <p>
          You can provide evidence to dispute to a credit/debit card chargeback via the Stripe dashboard. Direct Debit chargebacks are final and cannot be disputed. It is illegal for customers to make a fraudulent chargeback and chargebacks do not absolve customers of their payment obligations.
        </p>

        <p>
          The membership system will display information about all disputes and in future will mark monthly fee payments as failed if they are disputed.
        </p>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
