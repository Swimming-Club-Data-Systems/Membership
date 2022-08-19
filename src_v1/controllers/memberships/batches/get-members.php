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

// Get members for this user
$getMembers = $db->prepare("SELECT MForename, MSurname, MemberID FROM members WHERE UserID = ? AND Active ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $batch->user,
]);

// Get membership years
$today = new DateTime('now', new DateTimeZone('Europe/London'));

$getYears = $db->prepare("SELECT `ID` `id`, `Name` `name`, `StartDate` `start`, `EndDate` `end` FROM `membershipYear` WHERE `Tenant` = ? AND `EndDate` >= ? ORDER BY `StartDate` ASC, `EndDate` ASC, `Name` ASC");
$getYears->execute([
  $tenant->getId(),
  $today->format('Y-m-d'),
]);
$year = $getYears->fetch(PDO::FETCH_OBJ);

ob_clean();
ob_start();

?>

<form id="add-membership-form" class="needs-validation" novalidate>

  <div class="mb-3">
    <label class="form-label" for="member">Select member</label>
    <select class="form-select" id="member" name="member" required>
      <option disabled selected value="null">Select a member</option>
      <?php while ($member = $getMembers->fetch(PDO::FETCH_OBJ)) { ?>
        <option value="<?= htmlspecialchars($member->MemberID) ?>"><?= htmlspecialchars($member->MForename . ' ' . $member->MSurname) ?></option>
      <?php } ?>
    </select>
  </div>

  <div class="">
    <label for="membership-year" class="form-label">Membership year</label>
    <select class="form-select" id="membership-year" name="membership-year" required>
      <option disabled selected value="null">Choose a year</option>
      <?php do {
        $start = new DateTime($year->start, new DateTimeZone('Europe/London'));
        $end = new DateTime($year->end, new DateTimeZone('Europe/London'));
      ?>
        <option value="<?= htmlspecialchars($year->id) ?>"><?= htmlspecialchars($year->name) ?> (<?= htmlspecialchars($start->format('j M Y')) ?> - <?= htmlspecialchars($end->format('j M Y')) ?>)</option>
      <?php } while ($year = $getYears->fetch(PDO::FETCH_OBJ)); ?>
    </select>
  </div>

  <div id="add-membership-form-details"">

  </div>

</form>

<?php

$html = ob_get_clean();

// reportError($html);

header('content-type: application/json');
echo json_encode([
  'html' => $html
]);
