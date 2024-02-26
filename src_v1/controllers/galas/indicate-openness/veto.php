<?php

$db = app()->db;
$tenant = app()->tenant;

$sql = null;

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
  $sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE `EntryID` = ? AND members.UserID = ? ORDER BY `galas`.`GalaDate` DESC;");
  $sql->execute([
    $id,
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
  ]);
} else {
  $sql = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE `EntryID` = ? AND galas.Tenant = ? ORDER BY `galas`.`GalaDate` DESC;");
  $sql->execute([
    $id,
    $tenant->getId()
  ]);
}
$row = $sql->fetch(PDO::FETCH_ASSOC);

if ($row == null || !$row['Vetoable']) {
  halt(404);
}

$pagetitle = 'Veto ' . htmlspecialchars((string) $row['MForename']) . '\'s entry into ' . htmlspecialchars((string) $row['GalaName']);
include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>
        Veto <?=htmlspecialchars((string) $row['MForename'])?>'s entry into <?=htmlspecialchars((string) $row['GalaName'])?>
      </h1>
      <p class="lead">You are permitted to veto this entry. This will withdraw your entry from <?=htmlspecialchars((string) $row['GalaName'])?>.</p>

      <?php if (!$row['Locked']) { ?>
      <p>This entry has not been locked by your coach so you may wish to edit the swims <?=htmlspecialchars((string) $row['MForename'])?> is entered into.</p>
      <?php } ?>

      <p>
        <a href="<?=autoUrl("galas/entries/" . $id . "/veto/do")?>" class="btn btn-success">
          Veto this entry
        </a>
      </p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();