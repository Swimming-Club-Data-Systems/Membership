<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$session = \SCDS\Onboarding\Session::retrieve($id);

$db = app()->db;
$tenant = app()->tenant;

$getYear = $db->prepare("SELECT `Name`, `StartDate`, `EndDate` FROM `membershipYear` WHERE `ID` = ? AND `Tenant` = ?");
$getYear->execute([
  $_GET['year'],
  $tenant->getId(),
]);
$year = $getYear->fetch(PDO::FETCH_ASSOC);

if (!$year) halt(404);

if (!isset($session->user)) halt(404);

// Check user exists
$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $session->user
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$getMembers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn, NGBCategory ngb, ngbMembership.Name ngbName, ngbMembership.Fees ngbFees, ClubCategory club, clubMembership.Name clubName, clubMembership.Fees clubFees FROM members INNER JOIN clubMembershipClasses AS ngbMembership ON ngbMembership.ID = members.NGBCategory INNER JOIN clubMembershipClasses AS clubMembership ON clubMembership.ID = members.ClubCategory INNER JOIN onboardingMembers ON onboardingMembers.member = members.MemberID WHERE Active AND onboardingMembers.session = ? ORDER BY fn ASC, sn ASC");
$getMembers->execute([
  $session->id
]);
$member = $getMembers->fetch(PDO::FETCH_OBJ);

$getCurrentMemberships = $db->prepare("SELECT `Name` `name`, `Description` `description`, `Type` `type`, `memberships`.`Amount` `paid`, `clubMembershipClasses`.`Fees` `expectPaid` FROM `memberships` INNER JOIN clubMembershipClasses ON memberships.Membership = clubMembershipClasses.ID WHERE `Member` = ? AND `Year` = ?");
$hasMembership = $db->prepare("SELECT COUNT(*) FROM memberships WHERE `Member` = ? AND `Year` = ? AND `Membership` = ?");

$pagetitle = "New batch for " . htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) . " - " . htmlspecialchars($year['Name']) . " - Membership Centre";
include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('onboarding')) ?>">Onboarding</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("onboarding/a/$id")) ?>"><?= htmlspecialchars($session->getUser()->getName()) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Fees</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Membership fees for <?= htmlspecialchars(\SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])) ?>
        </h1>
        <p class="lead mb-0">
          For <?= htmlspecialchars($year['Name']) ?>
        </p>
      </div>
      <div class="col-auto ms-lg-auto">
        <a href="<?= htmlspecialchars(autoUrl("onboarding/a/$id")) ?>" class="btn btn-warning">Cancel</a>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <form method="post" id="form">

        <input type="hidden" name="year" value="<?= htmlspecialchars($_GET['year']) ?>">

        <p class="lead">
          Welcome to the fee editor.
        </p>

        <p>
          Select memberships to add for each member you are adding to <?= htmlspecialchars($info['Forename']) ?>'s account.
        </p>

        <p>
          You can make adjustments to the normal fee by editing the prices shown. <strong>Note that we won't automatically apply discounts for families. You'll need to make appropriate adjustments yourself to ensure the total is correct.</strong>
        </p>

        <p>
          Once complete, you can save this batch.
        </p>

        <?php if ($member) { ?>
          <ul class="list-group mb-3">
            <?php do {

              // Get memberships
              $getCurrentMemberships->execute([
                $member->id,
                $_GET['year'],
              ]);

              $membership = $getCurrentMemberships->fetch(PDO::FETCH_OBJ);

            ?>
              <li class="list-group-item">
                <h2><?= htmlspecialchars(\SCDS\Formatting\Names::format($member->fn, $member->sn)) ?></h2>

                <h3>Current Memberships for <?= htmlspecialchars($year['Name']) ?></h3>
                <?php if ($membership) { ?>
                  <ul class="mb-3">
                    <?php do { ?>
                      <li><?= htmlspecialchars($membership->name) ?>, Paid <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($membership->paid), 'GBP')) ?></li>
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
                  $_GET['year'],
                  $member->ngb,
                ]);
                $hasNgb = $hasMembership->fetchColumn() > 0;

                $hasMembership->execute([
                  $member->id,
                  $_GET['year'],
                  $member->club,
                ]);
                $hasClub = $hasMembership->fetchColumn() > 0;
                ?>

                <?php if ($hasClub && $hasNgb) { ?>
                  <div class="alert alert-info mb-0">
                    <p class="mb-0">
                      <strong>There are no additional memberships available for <?= htmlspecialchars($member->fn) ?></strong>
                    </p>
                  </div>
                <?php } ?>

                <?php if (!$hasNgb) { ?>

                  <div class="card card-body mb-2">

                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" id="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-yes') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-yes') ?>" data-type="membership-check" data-collapse-target="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-collapse') ?>">
                      <label class="form-check-label" for="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-yes') ?>">
                        Add <?= htmlspecialchars($member->ngbName) ?>
                      </label>
                    </div>

                    <div class="collapse pt-3" id="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-collapse') ?>">

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

                      <div class="">
                        <label for="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-notes') ?>" class="form-label">Notes <span class="text-muted">(optional)</span></label>
                        <textarea class="form-control" id="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-notes') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->ngb . '-notes') ?>" rows="3"></textarea>
                        <div class="small">Place explanatory notes here.</div>
                      </div>

                    </div>

                  </div>

                <?php } ?>

                <?php if (!$hasClub) { ?>

                  <div class="card card-body mb-2">

                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" id="<?= htmlspecialchars($member->id . '-' . $member->club . '-yes') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->club . '-yes') ?>" data-type="membership-check" data-collapse-target="<?= htmlspecialchars($member->id . '-' . $member->club . '-collapse') ?>">
                      <label class="form-check-label" for="<?= htmlspecialchars($member->id . '-' . $member->club . '-yes') ?>">
                        Add <?= htmlspecialchars($member->clubName) ?>
                      </label>
                    </div>

                    <div class="collapse pt-3" id="<?= htmlspecialchars($member->id . '-' . $member->club . '-collapse') ?>">

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

                      <div class="">
                        <label for="<?= htmlspecialchars($member->id . '-' . $member->club . '-notes') ?>" class="form-label">Notes <span class="text-muted">(optional)</span></label>
                        <textarea class="form-control" id="<?= htmlspecialchars($member->id . '-' . $member->club . '-notes') ?>" name="<?= htmlspecialchars($member->id . '-' . $member->club . '-notes') ?>" rows="3"></textarea>
                        <div class="small">Place explanatory notes here.</div>
                      </div>

                    </div>
                  </div>

                <?php } ?>
              </li>
            <?php } while ($member = $getMembers->fetch(PDO::FETCH_OBJ)); ?>
          </ul>

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
$footer->addJs('js/memberships/new-batch.js');
$footer->render();
