<?php

// Search for a user by name

$status = 200;
$html = null;
$error = null;
$disabled = false;

try {

  $db = app()->db;
  $tenant = app()->tenant;

  $search = '%' . trim($_POST["search"]) . '%';

  $sql = "";
  $search_terms = explode(' ', $_POST["search"]);
  $names = [];
  $names[] = $tenant->getId();
  $sql = "";
  for ($i = 0; $i < sizeof($search_terms); $i++) {
    if ($i > 0) {
      $sql .= " OR ";
    }
    $sql .= " Forename COLLATE utf8mb4_general_ci LIKE ? OR Surname COLLATE utf8mb4_general_ci LIKE ? ";
    for ($y = 0; $y < 2; $y++) {
      $names[] = "%" . $search_terms[$i] . "%";
    }
  }

  $sql = "SELECT Forename, Surname, UserID, EmailAddress FROM users INNER JOIN `permissions` ON users.UserID = `permissions`.`User` WHERE Tenant = ? AND Active AND (" . $sql . ") AND `permissions`.`Permission` = 'Parent' ORDER BY Forename ASC, Surname ASC";

  $users = $db->prepare($sql);
  $users->execute($names);

  ob_start();

?>

  <?php if ($user = $users->fetch(PDO::FETCH_ASSOC)) { ?>
    <option value="none">Select a user...</option>
    <?php do { ?>
      <option value="<?= htmlspecialchars($user['UserID']) ?>"><?= htmlspecialchars(\SCDS\Formatting\Names::format($user['Forename'], $user['Surname'])) ?> (<?= htmlspecialchars($user['EmailAddress']) ?>)</option>
    <?php } while ($user = $users->fetch(PDO::FETCH_ASSOC)); ?>
  <?php } else {
    $disabled = true; ?>
    <option value="none">No matches</option>
  <?php } ?>

<?php
  $html = ob_get_clean();
} catch (Exception $e) {
  $status = 500;
  $disabled = true;
  $html = '<option value="none">No matches</option>';

  reportError($e);
}

$json = [
  'status' => $status,
  'html' => trim($html),
  'error' => $error,
  'disabled' => $disabled,
];

header('content-type: application/json');
echo json_encode($json);
