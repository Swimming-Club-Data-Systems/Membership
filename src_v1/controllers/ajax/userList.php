<?php

$db = app()->db;
$tenant = app()->tenant;

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

if ($access != "Admin" && $access != "Galas") {
  halt(404);
}

$users = null;
if (isset($_POST["search"])) {
  // get the search term parameter from post
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

  $sql = "SELECT Forename, Surname, UserID FROM users WHERE Tenant = ? AND Active AND (" . $sql . ") ORDER BY Forename, Surname ASC";

  $users = $db->prepare($sql);
  $users->execute($names);
}

$getAccessLevels = $db->prepare("SELECT `Permission` FROM `permissions` WHERE User = ?");

if ($users == null) {
  halt(404);
}

$user = $users->fetch(PDO::FETCH_ASSOC);

if ($user != null) { ?>

  <div class="list-group">

    <?php do {
      $getAccessLevels->execute([
        $user['UserID']
      ]);
    ?>
      <a href="<?= autoUrl("users/" . $user['UserID']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
        <?= htmlspecialchars(\SCDS\Formatting\Names::format($user['Forename'], $user['Surname'])) ?>
        <div class="text-end">
          <?php while ($al = $getAccessLevels->fetchColumn()) { ?>
            <span class="badge bg-primary rounded-pill">
              <?= $al ?>
            </span>
          <?php } ?>
        </div>
      </a>
    <?php } while ($user = $users->fetch(PDO::FETCH_ASSOC)); ?>
  </div>

<?php } else { ?>

  <div class="alert alert-warning">
    <strong>No users found for that name</strong><br>
    Please try another search
  </div>

<?php } ?>