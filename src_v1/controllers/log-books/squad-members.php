<?php

$db = app()->db;
$tenant = app()->tenant;

$getSquadInfo = $db->prepare("SELECT SquadName FROM squads WHERE SquadID = ? AND Tenant = ?");
$getSquadInfo->execute([
  $squad,
  $tenant->getId()
]);
$squadInfo = $getSquadInfo->fetch(PDO::FETCH_ASSOC);

if (!$squadInfo) {
  halt(404);
}

$getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID WHERE squadMembers.Squad = ? ORDER BY fn ASC, sn ASC");
$getMembers->execute([$squad]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

$pagetitle = htmlspecialchars($squadInfo['SquadName'] . " member log books");

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("log-books")) ?>">Log Books</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($squadInfo['SquadName']) ?></li>
    </ol>
  </nav>

  <h1><?= htmlspecialchars($squadInfo['SquadName']) ?> log books</h1>
  <p class="lead">
    Members can log training sessions and other activity.
  </p>

  <p>
    <a href="<?= htmlspecialchars(autoUrl('log-books/squads/' . $squad . '/recent')) ?>">View most recent entries for this squad</a>
  </p>

  <div class="row">
    <div class="col-md-8">
      <?php if ($member) { ?>
        <div class="list-group mb-3">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl("log-books/members/" . $member['id'])) ?>" class="list-group-item list-group-item-action">
              <p class="mb-0">
                <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?>'s log book</strong>
              </p>
            </a>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>This squad has no members.</strong>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
