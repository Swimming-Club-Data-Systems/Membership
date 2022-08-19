<?php

use function GuzzleHttp\json_encode;

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$member = null;
try {
  $member = new Member($id);
} catch (Exception $e) {
  halt(404);
}

$user = $member->getUser();

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && (!$user || $user->getId() != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
  halt(404);
}

$date = new DateTime('now', new DateTimeZone('Europe/London'));

$getQualifications = $db->prepare("SELECT qualifications.ID, qualificationsMembers.ID AS QID, `Name`, `Description`, `ValidFrom`, `ValidUntil`, `Notes` FROM qualificationsMembers INNER JOIN qualifications ON qualificationsMembers.Qualification = qualifications.ID WHERE Member = :member AND ValidFrom <= :date AND (ValidUntil >= :date OR ValidUntil IS NULL) ORDER BY `Name` ASC");

$json = null;

try {

  $getQualifications->execute([
    'member' => $id,
    'date' => $date->format('Y-m-d'),
  ]);
  $qualification = $getQualifications->fetch(PDO::FETCH_ASSOC);

  ob_start();

?>

  <p>
    Qualifications are new. We're slowly adding additional features.
  </p>

  <?php if ($qualification) {
    $from = new DateTime($qualification['ValidFrom'], new DateTimeZone('Europe/London'));
    $to = null;
    if ($qualification['ValidUntil']) {
      $to = new DateTime($qualification['ValidUntil'], new DateTimeZone('Europe/London'));
    }
    ?>
    <ul class="list-group mb-3">
      <?php do { ?>
        <li class="list-group-item" id="<?= htmlspecialchars('qualification-' . $qualification['QID']) ?>">
          <p class="mb-0">
            <strong><?= htmlspecialchars($qualification['Name']) ?></strong>
          </p>
          <p class="mb-0">
            Valid since <em><?= htmlspecialchars($from->format('j F Y')) ?></em><?php if ($to) { ?> until <em><?= htmlspecialchars($to->format('j F Y')) ?></em><?php } ?>
          </p>
        </li>
      <?php } while ($qualification = $getQualifications->fetch(PDO::FETCH_ASSOC)); ?>
    </ul>
  <?php } else { ?>
  <div class="alert alert-warning">
    <p class="mb-0">
      <strong><?= htmlspecialchars($member->getForename()) ?> has no qualifications</strong>
    </p>
    <p class="mb-0">
      <a href="<?= htmlspecialchars(autoUrl("members/$id/qualifications/new")) ?>" class="alert-link">Add one</a> to get started.
    </p>
  </div>
  <?php } ?>

<?php

  $html = ob_get_clean();

  $json = [
    'status' => 200,
    'html' => $html,
  ];
} catch (Exception $e) {
  $json = [
    'status' => 500,
    'html' => '<p>Error</p>',
  ];
}

header('content-type: application/json');
echo json_encode($json);