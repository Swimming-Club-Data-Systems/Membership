<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT UserID FROM members WHERE MemberID = ? AND Tenant = ?");
$query->execute([
  $id,
  $tenant->getId()
]);
$result = $query->fetchColumn();

if ($result == null || $result != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
  halt(404);
}

$query = $db->prepare("SELECT COUNT(*) FROM moves WHERE MemberID = ?");
$query->execute([$id]);
$count = $query->fetchColumn();

$query = $db->prepare("SELECT MForename, MSurname FROM members WHERE MemberID = ?");
$query->execute([$id]);
$result = $query->fetch(PDO::FETCH_ASSOC);

$_SESSION['TENANT-' . app()->tenant->getId()]['LeaveKey'] = hash('md5', time());

include BASE_PATH . "views/header.php"; ?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <?php if ($count == 0 && $_SESSION['TENANT-' . app()->tenant->getId()]['ConfirmLeave'] !== true) { ?>
      <h1>
        We're sorry to see you go
      </h1>
      <p>
        Please confirm that <?=$result['MForename']?> will be leaving as of 1
        <?=date("F Y", strtotime('+1 month'))?>.
      </p>
      <p>
        <a href="<?=autoUrl("members/" . $id . "/leaveclub/" . $_SESSION['TENANT-' . app()->tenant->getId()]['LeaveKey'])?>" class="btn btn-danger">
          I Confirm
        </a>
      </p>
      <?php } else if ($_SESSION['TENANT-' . app()->tenant->getId()]['ConfirmLeave']) { ?>
      <h1>
        We're sorry to see you go
      </h1>
      <p>
        <?=$result['MForename']?> will be automatically removed from our
        registers and billing systems on 1 <?=date("F Y", strtotime('+1 month'))?>.
      </p>
      <p>
        To cancel your departure, please contact the membership secretary
      </p>
      <p>
        We hope to see you again one day.
      </p>
      <p>
        <a href="<?=autoUrl("members/" . $id)?>" class="btn btn-danger">
          Return to swimmer
        </a>
      </p>
      <?php } else { ?>
      <h1>
        An error occured
      </h1>
      <p>
        We're unable to let you use this self-service system right now.
      </p>
      <p>
        If you have already used this serice then you cannot use it again.
      </p>
      <p>
        Please speak to the membership secretary.
      </p>
      <?php } ?>
    </div>
  </div>
</div>

<?php

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ConfirmLeave'])) {
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['ConfirmLeave']);
}

$footer = new \SCDS\Footer();
$footer->render();
