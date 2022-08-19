<?php

$pagetitle = "Bulk Editors";

$db = app()->db;
$tenant = app()->tenant;

$getMembers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM members WHERE Tenant = ? AND Active ORDER BY fn ASC, sn ASC");
$getMembers->execute([
  $tenant->getId(),
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

$getSquads = $db->prepare("SELECT `SquadName` `name`, `SquadID` `id` FROM `squads` WHERE `Tenant` = ? ORDER BY `SquadFee` DESC, `SquadName` ASC");
$getSquads->execute([
  $tenant->getId(),
]);
$squads = $getSquads->fetchAll(PDO::FETCH_ASSOC);

$getIsMember = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad = ?");

$fluidContainer = true;
include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/editors")) ?>">Bulk editors</a></li>
      <li class="breadcrumb-item active" aria-current="page">Squad Membership</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Squad Membership Editor</h1>
      <p class="lead">Quickly edit which squads a member is in.</p>

      <p>
        About this editor;
      </p>

      <ul>
        <li>This editor page will not send confirmation emails to members.</li>
        <li>Changes are saved automatically but changes by other users will not be shown in real time.</li>
      </ul>
    </div>
  </div>

  <?php if ($member) { ?>

    <ul class="list-group" id="squad-list-group">
      <?php do { ?>

        <li class="list-group-item">
          <h2><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?></h2>

          <?php if (sizeof($squads) > 0) { ?>
            <div class="row">
              <?php foreach ($squads as $squad) {
                $getIsMember->execute([
                  $member['id'],
                  $squad['id'],
                ]);
                $isMember = $getIsMember->fetchColumn() > 0;
              ?>
                <div class="col">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="<?= htmlspecialchars('squad-member-check-squad-' . $squad['id'] . '-member-' . $member['id']) ?>" <?php if ($isMember) { ?>checked<?php } ?> data-member="<?= htmlspecialchars($member['id']) ?>" data-squad="<?= htmlspecialchars($squad['id']) ?>" data-member-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?>">
                    <label class="form-check-label" for="<?= htmlspecialchars('squad-member-check-squad-' . $squad['id'] . '-member-' . $member['id']) ?>"><?= htmlspecialchars($squad['name']) ?></label>
                  </div>
                </div>
              <?php } ?>
            </div>
          <?php } ?>
        </li>

      <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
    </ul>

  <?php } else { ?>
    <div class="alert alert-info">
      <p class="mb-0">
        <strong>There are no members to display</strong>
      </p>
      <p class="mb-0">
        Add a member first.
      </p>
    </div>
  <?php } ?>

</div>

<div id="options-data" data-ajax-url="<?= htmlspecialchars(autoUrl('admin/editors/squad-membership/add-remove')) ?>"></div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->addJs('public/js/admin/editors/squad-membership.js');
$footer->render();
