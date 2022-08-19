<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

// Get membership years
$today = new DateTime('now', new DateTimeZone('Europe/London'));

$getYears = $db->prepare("SELECT `ID` `id`, `Name` `name`, `StartDate` `start`, `EndDate` `end` FROM `membershipYear` WHERE `Tenant` = ? AND `EndDate` >= ? ORDER BY `StartDate` ASC, `EndDate` ASC, `Name` ASC");
$getYears->execute([
  $tenant->getId(),
  $today->format('Y-m-d'),
]);
$year = $getYears->fetch(PDO::FETCH_OBJ);

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

$pagetitle = "New batch for " . htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) . " - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("memberships/years")) ?>">Years</a></li>
        <li class="breadcrumb-item active" aria-current="page">New Batch</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          New batch for <?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?>
        </h1>
        <p class="lead mb-0">
          TEST
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

      <form method="post" id="form">

        <p class="lead">
          Welcome to the batch creator.
        </p>

        <p>
          Select memberships to add for each member connected to <?= htmlspecialchars($info['Forename']) ?>'s account.
        </p>

        <p>
          Add required details here. We'll assign memberships for this batch on the next page.
        </p>

        <p>
          <strong>When you create a batch manually, we won't automatically apply discounts for families. You'll need to make appropriate adjustments yourself to ensure the total is correct.</strong>
        </p>

        <p>
          Once complete, you can save this batch. If there is a fee to pay, we'll send an email notifying <?= htmlspecialchars($info['Forename']) ?> that they need to log in to pay membership fees. If there's no fee, we'll silently assign the appropriate memberships to the members.
        </p>

        <div class="mb-3">
          <label for="introduction-text" class="form-label">Introduction text <span class="text-muted">(optional)</span></label>
          <textarea class="form-control" id="introduction-text" name="introduction-text" rows="3"></textarea>
          <div class="small">We'll put any text you write here are the top of the email we send to <?= htmlspecialchars($info['Forename']) ?>. Formatting with Markdown is supported.</div>
        </div>

        <?php if ($member) { ?>

          <div class="mb-3">
            <label for="footer-text" class="form-label">Footer text <span class="text-muted">(optional)</span></label>
            <textarea class="form-control" id="footer-text" name="footer-text" rows="3"></textarea>
            <div class="small">We'll put any text you write here are the end of the email we send to <?= htmlspecialchars($info['Forename']) ?>. Formatting with Markdown is supported.</div>
          </div>

          <div class="mb-3">
            <label for="due-date" class="form-label">Due date</label>
            <input type="date" class="form-control" id="due-date" name="due-date" placeholder="YYYY-MM-DD">
            <div class="small">Leave blank for no due date.</div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="" id="automatic-reminders" name="automatic-reminders" disabled>
            <label class="form-check-label" for="automatic-reminders">
              Send automatic email reminders until the user pays or the due date has passed (coming soon)
            </label>
          </div>

          <div class="mb-3">
            <p class="mb-2">Payment Options</p>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="payment-card" name="payment-card" value="1" checked>
              <label class="form-check-label" for="payment-card">
                Credit/Debit card
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="payment-direct-debit" name="payment-direct-debit" value="1" checked>
              <label class="form-check-label" for="payment-direct-debit">
                Direct Debit
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="payment-cash" name="payment-cash" value="1" disabled>
              <label class="form-check-label" for="payment-cash">
                Cash
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="payment-cheque" name="payment-cheque" value="1" disabled>
              <label class="form-check-label" for="payment-cheque">
                Cheque
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="payment-wire" name="payment-wire" value="1" disabled>
              <label class="form-check-label" for="payment-wire">
                Bank Transfer
              </label>
            </div>
          </div>

          <p>
            <button type="submit" class="btn btn-primary">Create batch</button>
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
$footer->addJs('js/memberships/new-batch.js');
$footer->render();
