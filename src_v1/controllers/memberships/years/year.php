<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

$getYear = $db->prepare("SELECT `Name`, `StartDate`, `EndDate` FROM `membershipYear` WHERE `ID` = ? AND `Tenant` = ?");
$getYear->execute([
  $id,
  $tenant->getId(),
]);
$year = $getYear->fetch(PDO::FETCH_ASSOC);

if (!$year) halt(404);

$start = 0;

if (isset($_GET['page']) && ((int) $_GET['page']) != 0) {
  $page = (int) $_GET['page'];
  $start = ($page - 1) * 10;
} else {
  $page = 1;
}

// Get count
$getCount = $db->prepare("SELECT COUNT(DISTINCT(Member)) FROM memberships WHERE `Year` = ?;");
$getCount->execute([
  $id,
]);
$count = $getCount->fetchColumn();

// Paginate
$yearMembers = $db->prepare("SELECT DISTINCT MemberID member, MForename fn, MSurname sn, Active current, ASANumber reg FROM memberships INNER JOIN members ON memberships.Member = members.MemberID WHERE `Year` = :id ORDER BY sn ASC, fn ASC LIMIT :offset, :num;");
$yearMembers->bindValue(':id', $id, PDO::PARAM_STR);
$yearMembers->bindValue(':offset', $start, PDO::PARAM_INT);
$yearMembers->bindValue(':num', 30, PDO::PARAM_INT);
$yearMembers->execute();

$member = $yearMembers->fetch(PDO::FETCH_OBJ);

if ($page > 1 && !$member) halt(404);

// Query to get memberships for a member
$getMemberships = $db->prepare("SELECT memberships.Membership id, Amount amount, StartDate starts, EndDate ends, Purchased timePurchased, PaymentInfo paymentInfo, Notes notes, clubMembershipClasses.Type membershipType, clubMembershipClasses.Name `name`, clubMembershipClasses.Description `description` FROM memberships INNER JOIN clubMembershipClasses ON memberships.Membership = clubMembershipClasses.ID WHERE `Year` = ? AND `Member` = ?");

$pagination = new Zebra_Pagination();
$pagination->records($count);
$pagination->records_per_page(30);

$pagetitle = htmlspecialchars($year['Name']) . " - Membership Years - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships/years")) ?>">Years</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($year['Name']) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($year['Name']) ?>
        </h1>
        <p class="lead mb-0">
          Full membership details for <?= htmlspecialchars($year['Name']) ?>
        </p>
      </div>
      <div class="col-auto ms-lg-auto">
        <a href="<?= htmlspecialchars(autoUrl("memberships/years/$id/edit")) ?>" class="btn btn-success">Edit</a>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <p>
        <?= htmlspecialchars($count) ?> member<?php if ($count != 1) { ?>s<?php } ?> in <?= htmlspecialchars($year['Name']) ?>.
      </p>

      <?php if ($member) { ?>
        <ul class="list-group mb-3">
          <?php do {

            $getMemberships->execute([
              $id,
              $member->member,
            ]);

          ?>
            <li class="list-group-item">
              <h2>
                <?php if (!$member->current) { ?><s><?php } ?><?= htmlspecialchars(\SCDS\Formatting\Names::format($member->fn, $member->sn)) ?><?php if (!$member->current) { ?></s><small class="text-muted"> (left the club)</small><?php } ?>
              </h2>

              <dl class="row">
                <dt class="col-3">Registration Number</dt>
                <dd class="col-9"><?= htmlspecialchars($member->reg) ?></dd>
              </dl>

              <h3>Memberships</h3>

              <ul class="list-unstyled">
                <?php while ($membership = $getMemberships->fetch(PDO::FETCH_OBJ)) {

                  $purchased = new DateTime($membership->timePurchased, new DateTimeZone('UTC'));
                  $purchased->setTimezone(new DateTimeZone('Europe/London'));

                  $paymentInfo = json_decode($membership->paymentInfo);

                ?>
                  <li>
                    <div class="d-block">
                      <h4><?= htmlspecialchars($membership->name) ?></h4>

                      <p>
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#<?= htmlspecialchars('member-' . $member->member . '-' . $membership->id . '-collapse') ?>" aria-expanded="false" aria-controls="<?= htmlspecialchars('member-' . $member->member . '-' . $membership->id . '-collapse') ?>">
                          Show info <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </button>
                      </p>

                      <div class="collapse" id="<?= htmlspecialchars('member-' . $member->member . '-' . $membership->id . '-collapse') ?>">
                        <dl class="row">
                          <dt class="col-3">
                            Valid from
                          </dt>
                          <dd class="col-9">
                            <?= htmlspecialchars((new DateTime($membership->starts, new DateTimeZone('Europe/London')))->format('j F Y')) ?>
                          </dd>

                          <dt class="col-3">
                            Valid until
                          </dt>
                          <dd class="col-9">
                            <?= htmlspecialchars((new DateTime($membership->ends, new DateTimeZone('Europe/London')))->format('j F Y')) ?>
                          </dd>

                          <dt class="col-3">
                            Paid
                          </dt>
                          <dd class="col-9">
                            <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($membership->amount), 'GBP')) ?>
                          </dd>

                          <?php if ($paymentInfo->type == 'stripe') { ?>
                            <!-- Show info -->
                            <?php if ($paymentInfo->data->payment_intent->payment_method->type == 'card') { ?>
                              <dt class="col-3">
                                Payment card
                              </dt>
                              <dd class="col-9">
                                <?= htmlspecialchars(getCardBrand($paymentInfo->data->payment_intent->payment_method->card->brand)) ?> <?= htmlspecialchars($paymentInfo->data->payment_intent->payment_method->card->funding) ?> card ending <?= htmlspecialchars($paymentInfo->data->payment_intent->payment_method->card->last4) ?>
                              </dd>
                            <?php } ?>
                            <dt class="col-3">
                              Payment status
                            </dt>
                            <dd class="col-9 text-uppercase">
                              <?= htmlspecialchars($paymentInfo->data->payment_intent->status) ?>
                            </dd>
                          <?php } ?>

                          <dt class="col-3">
                            Purchased at
                          </dt>
                          <dd class="col-9">
                            <?= htmlspecialchars($purchased->format('H:i, j F Y')) ?>
                          </dd>
                        </dl>
                      </div>
                    </div>
                  </li>
                <?php } ?>
              </ul>

            </li>
          <?php } while ($member = $yearMembers->fetch(PDO::FETCH_OBJ)); ?>
        </ul>

        <?= $pagination->render() ?>

      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>There are no members for this membership year</strong>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
