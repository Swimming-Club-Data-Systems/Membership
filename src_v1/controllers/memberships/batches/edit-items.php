<?php

if (!isset($_POST['id'])) halt(404);

$id = $_POST['id'];

$user = app()->user;
$db = app()->db;
$tenant = app()->tenant;

$getBatch = $db->prepare("SELECT membershipBatch.ID id, membershipBatch.Completed completed, DueDate due, Total total, PaymentTypes payMethods, PaymentDetails payDetails, membershipBatch.User `user` FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.User WHERE membershipBatch.ID = ? AND users.Tenant = ?");
$getBatch->execute([
  $id,
  app()->tenant->getId(),
]);

$batch = $getBatch->fetch(PDO::FETCH_OBJ);

if (!$batch) halt(404);

if (!$user->hasPermission('Admin')) halt(404);

// Get batch items
$getBatchItems = $db->prepare("SELECT membershipBatchItems.ID id, membershipBatchItems.Membership membershipId, membershipBatchItems.Amount amount, membershipBatchItems.Notes notes, members.MForename firstName, members.MSurname lastName, members.ASANumber ngbId, clubMembershipClasses.Type membershipType, clubMembershipClasses.Name membershipName, clubMembershipClasses.Description membershipDescription, membershipYear.ID yearId, membershipYear.Name yearName, membershipYear.StartDate yearStart, membershipYear.EndDate yearEnd FROM membershipBatchItems INNER JOIN membershipYear ON membershipBatchItems.Year = membershipYear.ID INNER JOIN members ON members.MemberID = membershipBatchItems.Member INNER JOIN clubMembershipClasses ON clubMembershipClasses.ID = membershipBatchItems.Membership WHERE Batch = ?");
$getBatchItems->execute([
  $id
]);
$item = $getBatchItems->fetch(PDO::FETCH_OBJ);

$markdown = new \ParsedownExtra();
$markdown->setSafeMode(true);

$formattedTotal = MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($batch->total), 'GBP');

// Get membership years
$today = new DateTime('now', new DateTimeZone('Europe/London'));

$getYears = $db->prepare("SELECT `ID` `id`, `Name` `name`, `StartDate` `start`, `EndDate` `end` FROM `membershipYear` WHERE `Tenant` = ? AND `EndDate` >= ? ORDER BY `StartDate` ASC, `EndDate` ASC, `Name` ASC");

ob_clean();
ob_start();

?>

<?php if ($item) { ?>
  <?php do {
    $getYears->execute([
      $tenant->getId(),
      $today->format('Y-m-d'),
    ]);
  ?>
    <div class="card card-body mb-2">
      <form class="needs-validation" novalidate id="<?= htmlspecialchars($item->id . '-form') ?>">
        <h3><?= htmlspecialchars($item->firstName . ' ' . $item->lastName) ?></h3>
        <p class="lead"><?= htmlspecialchars($item->membershipName) ?></p>

        <input type="hidden" name="item-id" value="<?= htmlspecialchars($item->id) ?>">

        <div class="mb-3 row">
          <label for="<?= htmlspecialchars($item->id . '-membership-id') ?>" class="col-3 col-form-label">Membership ID</label>
          <div class="col-9">
            <input type="text" class="form-control" id="<?= htmlspecialchars($item->id . '-membership-id') ?>" name="membership-id" value="<?= htmlspecialchars($item->membershipId) ?>" readonly>
          </div>
        </div>

        <div class="mb-3 row">
          <label for="<?= htmlspecialchars($item->id . '-membership-year') ?>" class="col-3 col-form-label">Membership year</label>
          <div class="col-9">
            <select class="form-select" id="<?= htmlspecialchars($item->id . '-membership-year') ?>" name="membership-year" required>
              <option disabled value="null">Choose a year</option>
              <?php while ($year = $getYears->fetch(PDO::FETCH_OBJ)) {
                $start = new DateTime($year->start, new DateTimeZone('Europe/London'));
                $end = new DateTime($year->end, new DateTimeZone('Europe/London'));
              ?>
                <option <?php if ($item->yearId == $year->id) { ?>selected<?php } ?> value="<?= htmlspecialchars($year->id) ?>"><?= htmlspecialchars($year->name) ?> (<?= htmlspecialchars($start->format('j M Y')) ?> - <?= htmlspecialchars($end->format('j M Y')) ?>)</option>
              <?php } ?>
            </select>
          </div>
        </div>

        <div class="mb-3 row">
          <label for="<?= htmlspecialchars($item->id . '-amount') ?>" class="col-3 col-form-label">Amount</label>
          <div class="col-9">
            <div class="input-group">
              <span class="input-group-text" id="basic-addon1">&pound;</span>
              <input type="number" class="form-control" id="<?= htmlspecialchars($item->id . '-amount') ?>" name="amount" min="0" step="0.01" value="<?= htmlspecialchars(MoneyHelpers::intToDecimal($item->amount)) ?>">
            </div>
          </div>
        </div>

        <div class="mb-3 row">
          <label for="<?= htmlspecialchars($item->id . '-ngb-id') ?>" class="col-3 col-form-label">NGB ID</label>
          <div class="col-9">
            <input type="text" class="form-control" id="<?= htmlspecialchars($item->id . '-ngb-id') ?>" name="ngb-id" value="<?= htmlspecialchars($item->ngbId) ?>" readonly>
          </div>
        </div>

        <div class="mb-3 row">
          <label for="<?= htmlspecialchars($item->id . '-notes') ?>" class="col-3 col-form-label">Notes</label>
          <div class="col-9">
            <textarea class="form-control" id="<?= htmlspecialchars($item->id . '-notes') ?>" name="notes"><?php if ($item->notes) { ?><?= htmlspecialchars($item->notes) ?><?php } ?></textarea>
          </div>
        </div>

        <?php if ($item->membershipDescription) { ?>
          <div class="mb-3 row">
            <label class="col-3 col-form-label">Membership description</label>
            <div class="col-9">
              <?= $markdown->text($item->membershipDescription) ?>
            </div>
          </div>
        <?php } ?>

        <p class="mb-0">
          <button class="btn btn-success" type="submit" data-action="update">
            Save item
          </button>
          <button class="btn btn-danger" type="button" data-action="delete" data-id="<?= htmlspecialchars($item->id) ?>">
            Delete item
          </button>
        </p>
      </form>
    </div>
  <?php } while ($item = $getBatchItems->fetch(PDO::FETCH_OBJ)); ?>
<?php } else { ?>
  <div class="alert alert-info">
    <p class="mb-0">
      <strong>There are currently no memberships in this batch.</strong>
    </p>
  </div>
<?php } ?>

<?php

$html = ob_get_clean();

header('content-type: application/json');
echo json_encode([
  'total' => $batch->total,
  'listHtml' => $html,
  'formattedTotal' => $formattedTotal,
]);
