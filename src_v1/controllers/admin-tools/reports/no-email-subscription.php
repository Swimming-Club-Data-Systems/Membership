<?php

$db = app()->db;
$tenant = app()->tenant;

$pagetitle = "Users without a Notify email subscription";

$getUsers = $db->prepare("SELECT UserID, Forename, Surname, EmailAddress FROM users WHERE Active AND NOT EmailComms AND Tenant = ? ORDER BY Forename ASC, Surname ASC");
$getUsers->execute([
  $tenant->getId(),
]);

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/reports")) ?>">Reports</a></li>
      <li class="breadcrumb-item active" aria-current="page">No Sub</li>
    </ol>
  </nav>

  <h1>Users without a Notify email subscription</h1>
  <p class="lead">
    The following users will not recieve notify emails unless they are Force Sent.
  </p>

  <div class="row">
    <div class="col-lg-8">
      <?php if ($user = $getUsers->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="list-group">
          <?php do { ?>
            <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars(autoUrl('users/' . $user['UserID'])) ?>">
              <div><strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($user['Forename'], $user['Surname'])) ?></strong></div>
              <div class="text-truncate"><?= htmlspecialchars($user['EmailAddress']) ?></div>
            </a>
          <?php } while ($user = $getUsers->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-info">
          <p class="mb-0">
            <strong>All users will get notify emails</strong>
          </p>
          <p class="mb-0">
            No users have not opted in.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
