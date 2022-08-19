<?php

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

$date = new DateTime('now', new DateTimeZone('Europe/London'));

// Get current years
$getYears = $db->prepare("SELECT `ID`, `Name`, `StartDate`, `EndDate` FROM `membershipYear` WHERE `Tenant` = ? AND `StartDate` <= ? AND `EndDate` >= ? ORDER BY `StartDate` ASC, `EndDate` ASC, `Name` ASC");
$getYears->execute([
  $tenant->getId(),
  $date->format('Y-m-d'),
  $date->format('Y-m-d')
]);
$year = $getYears->fetch(PDO::FETCH_ASSOC);

// Get current membership classes
$getClasses = $db->prepare("SELECT DISTINCT Membership, clubMembershipClasses.Name, clubMembershipClasses.Description, clubMembershipClasses.Fees FROM memberships INNER JOIN members ON members.MemberID = memberships.Member INNER JOIN clubMembershipClasses ON memberships.Membership = clubMembershipClasses.ID WHERE members.UserID = ? AND `Year` = ? ORDER BY clubMembershipClasses.Name ASC");

$getMembershipMembers = $db->prepare("SELECT MForename, MSurname, Amount, StartDate, EndDate FROM memberships INNER JOIN members ON memberships.Member = members.MemberID WHERE Membership = ? AND `Year` = ? AND UserID = ? ORDER BY MSurname ASC, MForename ASC");

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) . " Information";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">

  <div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users")) ?>">Users</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users/$id")) ?>"><?= htmlspecialchars(mb_substr($info["Forename"], 0, 1, 'utf-8') . mb_substr($info["Surname"], 0, 1, 'utf-8')) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Current Memberships</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-sm-9 col-md-10 col-lg-11">
        <h1 class="mb-0">
          Current Memberships
          <small><?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?></small>
        </h1>
      </div>
    </div>

  </div>

</div>

<div class="container">

  <div class="row">
    <div class="col-lg-8">
      <p>
        This page displays memberships currently held by members associated with this user. It also shows the total paid for memberships by class.
      </p>

      <?php if ($year) { ?>
        <?php do {
          $yearTotal = 0;
        ?>

          <h2>
            <?= htmlspecialchars($year['Name']) ?>
          </h2>

          <?php
          $getClasses->execute([
            $id,
            $year['ID']
          ]);
          $class = $getClasses->fetch(PDO::FETCH_ASSOC);
          ?>

          <?php if ($class) { ?>
            <?php do { ?>

              <?php
              $getMembershipMembers->execute([
                $class['Membership'],
                $year['ID'],
                $id,
              ]);
              $memberships = $getMembershipMembers->fetch(PDO::FETCH_ASSOC);
              ?>
              <div class="card mb-3">
                <div class="card-header">
                  <?= htmlspecialchars($class['Name']) ?>
                </div>
                <div class="card-body py-2">
                  <?php if ($memberships) {
                    $total = 0;
                  ?>
                    <?php do {
                      $total += (int) $memberships['Amount'];
                    ?>
                      <div class="row mb-2 pb-2 align-items-center border-bottom">
                        <div class="col-9">
                          <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($memberships['MForename'], $memberships['MSurname'])) ?></strong><br>
                          <?= htmlspecialchars($memberships['StartDate']) ?> until <?= htmlspecialchars($memberships['EndDate']) ?>
                        </div>
                        <div class="col text-end fw-bold">
                          <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal((int) $memberships['Amount']), 'GBP')) ?>
                        </div>
                      </div>
                    <?php } while ($memberships = $getMembershipMembers->fetch(PDO::FETCH_ASSOC)); ?>
                    <div class="row">
                      <div class="col-9">
                        <strong>Total</strong> for this membership year
                      </div>
                      <div class="col text-end fw-bold">
                        <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal((int) $total), 'GBP')) ?>
                      </div>
                    </div>
                    <?php
                    $yearTotal += $total;
                    ?>
                  <?php } else { ?>
                    <div class="alert alert-danger">
                      An error occurred - there should be members listed here
                    </div>
                  <?php } ?>
                </div>
              </div>
            <?php } while ($class = $getClasses->fetch(PDO::FETCH_ASSOC)); ?>

            <div class="px-3 mb-3">
              <div class="row align-items-center">
                <div class="col-9">
                  Total paid for this membership year<br>(Across all membership classes)
                </div>
                <div class="col text-end fw-bold">
                  <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal((int) $yearTotal), 'GBP')) ?>
                </div>
              </div>
            </div>

          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>
                  No memberships held for this membership year.
                </strong>
              </p>
            </div>
          <?php } ?>

        <?php } while ($year = $getYears->fetch(PDO::FETCH_ASSOC)); ?>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>
              No membership years to display.
            </strong>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
