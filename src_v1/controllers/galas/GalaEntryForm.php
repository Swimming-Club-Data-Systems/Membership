<?php

$db = app()->db;
$tenant = app()->tenant;

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Enter a Gala";

$swimmerCount = 0;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $count = $db->prepare("SELECT COUNT(*) FROM `members` WHERE `members`.`UserID` = ?");
  $count->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
  ]);
  $swimmerCount = $count->fetchColumn();
} else if (isset($swimmer)) {
  $count = $db->prepare("SELECT COUNT(*) FROM `members` WHERE `members`.`MemberID` = ? AND Tenant = ?");
  $count->execute([
    $swimmer,
    $tenant->getId()
  ]);
  $swimmerCount = $count->fetchColumn();
}

$mySwimmers = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $mySwimmers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM `members` WHERE `members`.`UserID` = ? ORDER BY fn ASC, sn ASC");
  $mySwimmers->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} else if (isset($swimmer)) {
  $mySwimmers = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM `members` WHERE `members`.`MemberID` = ? AND Tenant = ?");
  $mySwimmers->execute([
    $swimmer,
    $tenant->getId()
  ]);
}

$now = new DateTime('now', new DateTimeZone('Europe/London'));
$galas = $db->prepare("SELECT GalaID id, GalaName `name` FROM `galas` WHERE Tenant = ? AND ClosingDate >= ? ORDER BY `galas`.`ClosingDate` ASC");
$galas->execute([
  $tenant->getId(),
  $now->format('Y-m-d H:i:s')
]);

$mySwimmer = $mySwimmers->fetch(PDO::FETCH_ASSOC);
$gala = $galas->fetch(PDO::FETCH_ASSOC);

$hasSwimmer = true;
$hasGalas = true;
if ($mySwimmer == null) {
  $hasSwimmer = false;
}
if ($gala == null) {
  $hasGalas = false;
}

include BASE_PATH . "views/header.php";
include "galaMenu.php";
?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= autoUrl("members") ?>">Swimmers</a></li>
          <li class="breadcrumb-item"><a href="<?= autoUrl("members/" . $swimmer) ?>"><?= htmlspecialchars($mySwimmer['fn']) ?> <?= htmlspecialchars(mb_substr($mySwimmer['sn'], 0, 1, 'utf-8')) ?></a></li>
          <li class="breadcrumb-item active" aria-current="page">Enter a gala</li>
        </ol>
      </nav>
    <?php } else { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("galas")) ?>">Galas</a></li>
          <li class="breadcrumb-item active" aria-current="page">Enter gala</li>
        </ol>
      </nav>
    <?php } ?>

    <div id="gala-data" data-ajax-url="<?= htmlspecialchars(autoUrl('galas/ajax/entryForm')) ?>"></div>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1 class="mb-0">Enter a gala</h1>
      </div>
      <div class="col text-lg-end">
        <div class="d-lg-none mb-3"></div>
        <aside>
          <a href="<?= htmlspecialchars(autoUrl("galas/entergala/help")) ?>" class="btn btn-info">
            Gala entry help <i class="fa fa-info-circle" aria-hidden="true"></i>
          </a>
        </aside>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <?php if ($hasSwimmer && $hasGalas) { ?>
    <div class="">
      <form method="post">
        <h2>Select member and competition</h2>
        <div class="mb-3">
          <label class="form-label" for="swimmer">Select member</label>
          <select class="form-select" id="swimmer" name="swimmer" required>
            <option value="null" <?php if ($swimmerCount > 1) { ?>selected<?php } ?> disabled>Select a swimmer</option>
            <?php do { ?>
              <option value="<?= htmlspecialchars($mySwimmer['id']) ?>" <?php if ($swimmerCount == 1) { ?>selected<?php } ?>>
                <?= htmlspecialchars(\SCDS\Formatting\Names::format($mySwimmer['fn'], $mySwimmer['sn'])) ?>
              </option>
            <?php } while ($mySwimmer = $mySwimmers->fetch(PDO::FETCH_ASSOC)); ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" for="gala">Select competition</label>
          <select class="form-select" id="gala" name="gala" required>
            <option value="null" disabled selected>Select a gala</option>
            <?php do { ?>
              <option value="<?= htmlspecialchars($gala['id']) ?>">
                <?= htmlspecialchars($gala['name']) ?>
              </option>
            <?php } while ($gala = $galas->fetch(PDO::FETCH_ASSOC)); ?>
          </select>
        </div>
        <div class="ajaxArea mb-3" id="output">
          <div class="ajaxPlaceholder">Select a swimmer and gala
          </div>
        </div>
        <p>
          <button type="submit" id="submit" disabled class="btn btn-success">Submit</button>
        </p>
    </div>
    </form>
  <?php
  } else { ?>
    <p class="lead">
      We're unable to let you enter a gala at the moment
    </p>
    <p>
      This is because;
    </p>

    <?php if (!$hasSwimmer) { ?>
      <div class="alert alert-warning">
        <strong>You don't have any swimmers associated with your account</strong> <br>
        Please <a href="<?= autoUrl("my-account/addswimmer") ?>" class="alert-link">add some swimmers in My Account</a>, then try again
      </div>
    <?php } ?>

    <?php if (!$hasGalas) { ?>
      <div class="alert alert-danger">
        <strong>There are no galas open for entries at the moment</strong>
      </div>
    <?php } ?>

  <?php } ?>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/numerical/bignumber.min.js");
$footer->addJS("js/gala-entries/NewEntry.js");
$footer->render();
