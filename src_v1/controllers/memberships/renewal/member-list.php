<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

// Get membership years
$today = new DateTime('now', new DateTimeZone('Europe/London'));

$renewal = \SCDS\Onboarding\Renewal::retrieve($id);

$fluidContainer = true;

if (!$renewal) halt(404);

// $startDate = new DateTime($renewal->start, new DateTimeZone('Europe/London'));
// $endDate = new DateTime($renewal->end, new DateTimeZone('Europe/London'));
// $yearStart = new DateTime($renewal->yearStart, new DateTimeZone('Europe/London'));
// $yearEnd = new DateTime($renewal->yearEnd, new DateTimeZone('Europe/London'));

$stages = SCDS\Onboarding\Session::getDefaultRenewalStages();
$stageNames = SCDS\Onboarding\Session::stagesOrder();
$memberStages = SCDS\Onboarding\Member::getDefaultStages();
$memberStageNames = SCDS\Onboarding\Member::stagesOrder();

$basePageTitle = htmlspecialchars($renewal->start->format('j M Y') . " - " . $renewal->end->format('j M Y')) . " - Renewal - Membership Centre";
$pagetitle = "Member List - " . $basePageTitle;

$getCount = $getMembers = null;
// If else
if ($type == "member-list") {
  $getCount = $db->prepare("SELECT COUNT(*) FROM onboardingMembers INNER JOIN members ON onboardingMembers.member = members.MemberID INNER JOIN onboardingSessions ON onboardingMembers.session = onboardingSessions.id WHERE onboardingSessions.renewal = ?");
  $getCount->execute([
    $id
  ]);
  $getMembers = $db->prepare("SELECT members.MemberID memberId, members.MForename `firstName`, members.MSurname `lastName`, users.Forename uFirstName, users.Surname uLastName, onboardingSessions.completed_at completedAt, onboardingSessions.id sessionId, membershipBatch.Total FROM (onboardingMembers INNER JOIN members ON onboardingMembers.member = members.MemberID INNER JOIN onboardingSessions ON onboardingMembers.session = onboardingSessions.id) LEFT JOIN users ON members.UserID = users.UserID LEFT JOIN membershipBatch ON onboardingSessions.batch = membershipBatch.ID WHERE onboardingSessions.renewal = :sessionId ORDER BY onboardingSessions.completed_at DESC, MSurname ASC, MForename ASC");
  $getMembers->bindValue(':sessionId', $id, PDO::PARAM_STR);
  $getMembers->execute();
  $pagetitle = "Members in this renewal - " . $basePageTitle;
} else if ($type = "current-members-not-in-renewal-list") {
  $pagetitle = "Current members not in this renewal - " . $basePageTitle;
} else {
  halt(404);
}

$getBatchItems = $db->prepare("SELECT membershipBatchItems.Amount, membershipYear.Name yearName, clubMembershipClasses.Name, clubMembershipClasses.Description, clubMembershipClasses.Fees, clubMembershipClasses.Type FROM membershipBatchItems INNER JOIN clubMembershipClasses ON membershipBatchItems.Membership = clubMembershipClasses.ID INNER JOIN membershipYear ON  membershipBatchItems.Year = membershipYear.ID WHERE Member = ? AND Batch = ?");

$count = $getCount->fetchColumn();
$member = $getMembers->fetch(PDO::FETCH_OBJ);

include BASE_PATH . "views/header.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-fluid">

    <!-- Page header -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships")) ?>">Memberships</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships/renewal")) ?>">Renewal</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="<?= htmlspecialchars(autoUrl("memberships/renewal/$id")) ?>"><?= htmlspecialchars($renewal->start->format('d/m/Y')) ?> - <?= htmlspecialchars($renewal->end->format('d/m/Y')) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Member list</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Member list for <?= htmlspecialchars($renewal->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->end->format('j M Y')) ?> Renewal
        </h1>
        <p class="lead mb-0">
          For the <?php if ($renewal->clubYear) { ?> club membership year <?= htmlspecialchars($renewal->clubYear->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->clubYear->end->format('j M Y')) ?><?php } ?><?php if ($renewal->clubYear && $renewal->ngbYear) { ?> and the <?php } ?><?php if ($renewal->ngbYear) { ?> Swim England membership year <?= htmlspecialchars($renewal->ngbYear->start->format('j M Y')) ?> - <?= htmlspecialchars($renewal->ngbYear->end->format('j M Y')) ?><?php } ?>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">

  <div class="row">
    <div class="col">
      <main>

        <?php if ($member) { ?>

          <p>
            <?= htmlspecialchars($count) ?> records
          </p>

          <?php if ($type == "member-list") { ?>
            <p>
              A renewal session was created for members on this list. Some members may however have chosen not to renew.
            </p>
          <?php } ?>

          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>
                  Completed At
                </th>
                <th>
                  User Name
                </th>
                <th>
                  Member Name
                </th>
                <th>
                  Memberships
                </th>
                <th class="text-end">
                  Total Payment
                </th>
              </tr>
            </thead>
            <tbody>
              <?php do {
                $session = null;
                $memberOnboarding = null;
                $batchItem = null;
                try {
                  $session = SCDS\Onboarding\Session::retrieve($member->sessionId);
                  $memberOnboarding = SCDS\Onboarding\Member::retrieve($member->memberId, $member->sessionId);
                  $getBatchItems->execute([
                    $member->memberId,
                    $session->batch,
                  ]);
                  $batchItem = $getBatchItems->fetch(PDO::FETCH_OBJ);
                } catch (Exception $e) {
                  // Ignore
                }
              ?>
                <tr>
                  <td>
                    <?php if ($member->completedAt) {
                      $time = new DateTime($member->completedAt, new DateTimeZone('UTC'));
                      $time->setTimezone(new DateTimeZone('Europe/London'));
                    ?>
                      <?= htmlspecialchars($time->format("H:i d/m/y")) ?>
                    <?php } else { ?>
                      Incomplete
                    <?php } ?>
                  </td>
                  <td>
                    <?php if ($member->uFirstName) { ?>
                      <?= htmlspecialchars(\SCDS\Formatting\Names::format($member->uFirstName, $member->uLastName)) ?>
                    <?php } else { ?>
                      No user currently assigned
                    <?php } ?>
                  </td>
                  <td>
                    <?= htmlspecialchars(\SCDS\Formatting\Names::format($member->firstName, $member->lastName)) ?>
                  </td>
                  <td>
                    <?php if ($batchItem) { ?>
                    <ul class="mb-0 list-unstyled">
                      <?php do { ?>
                      <li><?= htmlspecialchars($batchItem->Name) ?> (<?= htmlspecialchars($batchItem->yearName) ?>), <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($batchItem->Amount), 'gbp')) ?></li>
                      <?php } while ($batchItem = $getBatchItems->fetch(PDO::FETCH_OBJ)); ?>
                    </ul>
                    <?php } else { ?>
                      -
                    <?php } ?>
                  </td>
                  <td class="font-monospace text-end">
                    <?php if ($member->completedAt && $member->Total) { ?>
                      <?= htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($member->Total), 'gbp')) ?>
                    <?php } ?>
                  </td>
                </tr>
              <?php } while ($member = $getMembers->fetch(PDO::FETCH_OBJ)); ?>
            </tbody>
          </table>

        <?php } else { ?>
          <div class="alert alert-warning">
            No records to display
          </div>
        <?php } ?>


      </main>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
