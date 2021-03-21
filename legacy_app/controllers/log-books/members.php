<?php

$db = nezamy_app()->db;
$tenant = nezamy_app()->tenant;

$getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members WHERE members.UserID = ? ORDER BY fn ASC, sn ASC");
$getMembers->execute([
  $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['UserID']
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Member log books";

include BASE_PATH . 'views/header.php';

?>

<div class="container">

  <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Members</li>
      </ol>
    </nav>

  <h1>Log books</h1>
  <p class="lead">
    Members can log training sessions and other activity.
  </p>

  <div class="row">
    <div class="col-md-8">
      <?php if ($member) { ?>
      <div class="list-group mb-3">
        <?php do { ?>
        <a href="<?=htmlspecialchars(autoUrl("log-books/members/" . $member['id']))?>" class="list-group-item list-group-item-action">
          <p class="mb-0">
            <strong><?=htmlspecialchars($member['fn'] . ' ' . $member['sn'])?>'s log book</strong>
          </p>
        </a>
        <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
      </div>
      <?php } else { ?>
      <div class="alert alert-warning">
        <p class="mb-0">
          <strong>There are no members linked to your account.</strong>
        </p>
      </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();