<?php

$db = app()->db;
$tenant = app()->tenant;
// $user = $id;

// $object = MembershipFees::getByUser($user);

// pre($object);

// pre($object->getFormattedTotal());

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$info = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent' && isset($id) && $id != null) {
  $user = $id;
  $userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ? AND Tenant = ?");
  $userInfo->execute([
    $id,
    $tenant->getId()
  ]);
  $info = $userInfo->fetch(PDO::FETCH_ASSOC);

  $isParent = $db->prepare("SELECT COUNT(*) FROM permissions WHERE User = ? AND Permission = 'Parent'");
  $isParent->execute([$id]);

  if ($info == null || $isParent->fetchColumn() == 0) {
    halt(404);
  }
}

$month = (new DateTime('now', new DateTimeZone('Europe/London')))->format('m');

$discounts = json_decode(app()->tenant->getKey('MembershipDiscounts'), true);
$clubDiscount = $swimEnglandDiscount = 0;
if ($discounts != null && isset($discounts['CLUB'][$month])) {
  $clubDiscount = $discounts['CLUB'][$month];
}
if ($discounts != null && isset($discounts['ASA'][$month])) {
  $swimEnglandDiscount = $discounts['ASA'][$month];
}

$sql = $db->prepare("SELECT COUNT(*) FROM `members` WHERE `members`.`UserID` = ?");
$sql->execute([$user]);

$payingSwimmerCount = $sql->fetchColumn();

$clubFees = MembershipFees\MembershipFees::getByUser($user);

$totalFeeString = $clubFees->getFormattedTotal();

$pagetitle = "Membership Fees";

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= autoUrl("users") ?>">Users</a></li>
          <li class="breadcrumb-item"><a href="<?= autoUrl("users/" . $id) ?>"><?= htmlspecialchars(mb_substr($info["Forename"], 0, 1, 'utf-8') . mb_substr($info["Surname"], 0, 1, 'utf-8')) ?></a></li>
          <li class="breadcrumb-item active" aria-current="page">Annual membership</li>
        </ol>
      </nav>
    <?php } else { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
          <li class="breadcrumb-item active" aria-current="page">Annual membership</li>
        </ol>
      </nav>
    <?php } ?>

    <div class="row">
      <div class="col-lg-8">
        <h1>Membership fees<?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') { ?> for <?= htmlspecialchars($info['Forename']) ?><?php } ?></h1>
        <p class="lead mb-0">Club and Swim England membership fees are paid yearly and are due on 1 January.</p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <p>The fees you're charged are dependent on the number of members linked to this account and the type of each member.</p>

      <h2>Your Membership Fees</h2>
      <div class="table-responsive-md">
        <table class="table table-light">
          <thead>
            <tr class="">
              <th>
                Club Membership
              </th>
              <th>
              </th>
            </tr>
          </thead>
          <?php foreach ($clubFees->getClubClasses() as $class) { ?>
            <thead class="">
              <tr class="">
                <th>
                  <?= htmlspecialchars($class->getName()) ?>
                </th>
                <th>
                </th>
              </tr>
            </thead>
            <thead>
              <tr>
                <th>
                  Member
                </th>
                <th>
                  Fee
                </th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($class->getFeeItems() as $item) { ?>
                <tr>
                  <td>
                    <?= htmlspecialchars($item->getDescription()) ?>
                  </td>
                  <td>
                    &pound;<?= htmlspecialchars($item->getFormattedAmount()) ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          <?php } ?>
          <thead class="">
            <tr class="">
              <th>
                <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership
              </th>
              <th>
              </th>
            </tr>
          </thead>
          <?php foreach ($clubFees->getNGBClasses() as $class) { ?>
            <thead class="">
              <tr class="">
                <th>
                  <?= htmlspecialchars($class->getName()) ?>
                </th>
                <th>
                </th>
              </tr>
            </thead>
            <thead>
              <tr>
                <th>
                  Member
                </th>
                <th>
                  Fee
                </th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($class->getFeeItems() as $item) { ?>
                <tr>
                  <td>
                    <?= htmlspecialchars($item->getDescription()) ?>
                  </td>
                  <td>
                    &pound;<?= htmlspecialchars($item->getFormattedAmount()) ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          <?php } ?>
          <tbody>
            <tr class="table-active">
              <td>
                Total Membership Fee
              </td>
              <td>
                &pound;<?= $totalFeeString ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
