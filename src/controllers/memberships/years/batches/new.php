<?php

$db = app()->db;
$tenant = app()->tenant;

$getYear = $db->prepare("SELECT `Name`, `StartDate`, `EndDate` FROM `membershipYear` WHERE `ID` = ? AND `Tenant` = ?");
$getYear->execute([
  $id,
  $tenant->getId(),
]);
$year = $getYear->fetch(PDO::FETCH_ASSOC);

if (!$year) halt(404);

if (!isset($_GET['user'])) halt(404);

// Check user exists
$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $_GET['user']
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$getMembers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn, NGBCategory ngb, ngbMembership.Name ngbName, ngbMembership.Fees ngbFees, ClubCategory club, clubMembership.Name clubName, clubMembership.Fees clubFees FROM members INNER JOIN clubMembershipClasses AS ngbMembership ON ngbMembership.ID = members.NGBCategory INNER JOIN clubMembershipClasses AS clubMembership ON clubMembership.ID = members.ClubCategory WHERE Active AND UserID = ? ORDER BY fn ASC, sn ASC");
$getMembers->execute([
  $_GET['user']
]);
$member = $getMembers->fetch(PDO::FETCH_OBJ);

$getCurrentMemberships = $db->prepare("SELECT `Name` `name`, `Description` `description`, `Type` `type`, `memberships`.`Amount` `paid`, `clubMembershipClasses`.`Fees` `expectPaid` FROM `memberships` INNER JOIN clubMembershipClasses ON memberships.Membership = clubMembershipClasses.ID WHERE `Member` = ? AND `Year` = ?");
$hasMembership = $db->prepare("SELECT COUNT(*) FROM memberships WHERE `Member` = ? AND `Year` = ? AND `Membership` = ?");

$pagetitle = "New batch for " . htmlspecialchars($info['Forename'] . ' ' . $info['Surname']) . " - " . htmlspecialchars($year['Name']) . " - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships/years")) ?>">Years</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships/years/$id")) ?>"><?= htmlspecialchars($year['Name']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">New Batch</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          New batch for <?= htmlspecialchars($info['Forename'] . ' ' . $info['Surname']) ?>
        </h1>
        <p class="lead mb-0">
          For <?= htmlspecialchars($year['Name']) ?>
        </p>
      </div>
      <div class="col-auto ms-lg-auto">
        <a href="<?= htmlspecialchars(autoUrl("users/" . urlencode($_GET['user']))) ?>" class="btn btn-warning">Cancel</a>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <form method="post">

        <p class="lead">
          Welcome to the batch creator.
        </p>

        <p>
          Select memberships to add for each member connected to <?= htmlspecialchars($info['Forename']) ?>'s account.
        </p>

        <p>
          You can make adjustments to the normal fee by editing the prices shown. <strong>When you create a batch manually, we won't automatically apply discounts for families. You'll need to make appropriate adjustments yourself.</strong>
        </p>

        <p>
          Once complete, you can save this batch. If there is a fee to pay, we'll send an email notifying <?= htmlspecialchars($info['Forename']) ?> that they need to log in to pay membership fees. If there's no fee, we'll silently assign the appropriate memberships to the members.
        </p>

        <div class="mb-3">
          <label for="introduction-text" class="form-label">Introduction text</label>
          <textarea class="form-control" id="introduction-text" name="introduction-text" rows="3"></textarea>
          <div class="small">We'll put any text you write here are the top of the email we send to <?= htmlspecialchars($info['Forename']) ?>. Formatting with Markdown is supported.</div>
        </div>

        <?php if ($member) { ?>
          <ul class="list-group mb-3">
            <?php do {

              // Get memberships
              $getCurrentMemberships->execute([
                $member->id,
                $id,
              ]);

              $membership = $getCurrentMemberships->fetch(PDO::FETCH_OBJ);

            ?>
              <li class="list-group-item">
                <h2><?= htmlspecialchars($member->fn . ' ' . $member->sn) ?></h2>

                <h3>Current Memberships for <?= htmlspecialchars($year['Name']) ?></h3>
                <?php if ($membership) { ?>
                  <ul>
                    <?php do { ?>
                      <li><?= htmlspecialchars($membership->name) ?>, Paid &pound;<?= htmlspecialchars($membership->paid) ?></li>
                    <?php } while ($membership = $getCurrentMemberships->fetch(PDO::FETCH_OBJ)); ?>
                  </ul>
                <?php } else { ?>
                  <div class="alert alert-warning">
                    <p class="mb-0">
                      <strong><?= htmlspecialchars($member->fn) ?> has no existing memberships for <?= htmlspecialchars($year['Name']) ?></strong>
                    </p>
                  </div>
                <?php } ?>

                <h3>Add New Memberships</h3>
                <?php
                $hasMembership->execute([
                  $member->id,
                  $id,
                  $member->ngb,
                ]);
                $has = $hasMembership->fetchColumn() > 0;
                ?>
                <?php if (!$has) { ?>

                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-yes') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-yes') ?>">
                    <label class="form-check-label" for="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-yes') ?>">
                      Add <?= htmlspecialchars($member->ngbName) ?>
                    </label>
                  </div>

                  <div class="collapse pt-3">

                    <?php
                    $fee = (json_decode($member->ngbFees))->fees[0];
                    ?>

                    <div class="mb-3">
                      <div class="mb-3">
                        <label for="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-amount') ?>" class="form-label">Fee</label>
                        <div class="input-group mb-3">
                          <span class="input-group-text">&pound;</span>
                          <input type="num" class="form-control" id="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-amount') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-amount') ?>" min="0" step="0.01" placeholder="0" value="<?= htmlspecialchars(MoneyHelpers::intToDecimal(($fee))) ?>">
                        </div>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label for="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-notes') ?>" class="form-label">Notes</label>
                      <textarea class="form-control" id="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-notes') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-notes') ?>" rows="3"></textarea>
                      <div class="small">Place explanatory notes here.</div>
                    </div>

                  </div>

                <?php } ?>

                <?php
                $hasMembership->execute([
                  $member->id,
                  $id,
                  $member->club,
                ]);
                $has = $hasMembership->fetchColumn() > 0;
                ?>
                <?php if (!$has) { ?>

                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="<?= htmlspecialchars($member->id . '-' . $member->club . '-yes') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->club . '-yes') ?>">
                    <label class="form-check-label" for="<?= htmlspecialchars($member->id . '-' . $member->club . '-yes') ?>">
                      Add <?= htmlspecialchars($member->clubName) ?>
                    </label>
                  </div>

                  <div class="collapse pt-3">

                    <?php
                    $fee = (json_decode($member->clubFees))->fees[0];
                    ?>

                    <div class="mb-3">
                      <div class="mb-3">
                        <label for="<?= htmlspecialchars($member->id . '-' . $member->club . '-amount') ?>" class="form-label">Fee</label>
                        <div class="input-group mb-3">
                          <span class="input-group-text">&pound;</span>
                          <input type="num" class="form-control" id="<?= htmlspecialchars($member->id . '-' . $member->club . '-amount') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->club . '-amount') ?>" min="0" step="0.01" placeholder="0" value="<?= htmlspecialchars(MoneyHelpers::intToDecimal(($fee))) ?>">
                        </div>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label for="<?= htmlspecialchars($member->id . '-' . $member->club . '-notes') ?>" class="form-label">Notes</label>
                      <textarea class="form-control" id="<?= htmlspecialchars($member->id . '-' . $member->club . '-notes') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->club . '-notes') ?>" rows="3"></textarea>
                      <div class="small">Place explanatory notes here.</div>
                    </div>

                  </div>

                <?php } ?>
              </li>
            <?php } while ($member = $getMembers->fetch(PDO::FETCH_OBJ)); ?>
          </ul>

          <div class="mb-3">
            <label for="footer-text" class="form-label">Footer text</label>
            <textarea class="form-control" id="footer-text" name="footer-text" rows="3"></textarea>
            <div class="small">We'll put any text you write here are the end of the email we send to <?= htmlspecialchars($info['Forename']) ?>. Formatting with Markdown is supported.</div>
          </div>

          <div class="mb-3">
            <label for="due-date" class="form-label">Due date</label>
            <input type="date" class="form-control" id="due-date" name="due-date" placeholder="YYYY-MM-DD">
            <div class="small">Leave blank for no due date.</div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="" id="automatic-reminders" name="automatic-reminders" checked>
            <label class="form-check-label" for="automatic-reminders">
              Send automatic email reminders until the user pays or the due date has passed
            </label>
          </div>

          <div class="mb-3">
            <p class="mb-2">Payment Options</p>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="payment-card" name="payment-card" checked>
              <label class="form-check-label" for="payment-card">
                Credit/Debit card
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="payment-direct-debit" name="payment-direct-debit" checked>
              <label class="form-check-label" for="payment-direct-debit">
                Direct Debit
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="payment-cash" name="payment-cash">
              <label class="form-check-label" for="payment-cash">
                Cash
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="payment-cheque" name="payment-cheque">
              <label class="form-check-label" for="payment-cheque">
                Cheque
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="payment-wire" name="payment-wire">
              <label class="form-check-label" for="payment-wire">
                Bank Transfer
              </label>
            </div>
          </div>

          <p>
            <button type="submit" class="btn btn-primary">Submit</button>
          </p>
        <?php } else { ?>
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>There are no members for this user</strong>
            </p>
          </div>
        <?php } ?>

      </form>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
