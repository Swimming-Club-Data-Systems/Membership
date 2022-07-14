<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId()
]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT COUNT(*) FROM joinSwimmers WHERE Parent = ? AND Tenant = ?");
$query->execute([
  $hash,
  $tenant->getId()
]);

$all = $false;
$deleteAll = false;
if ($query->fetchColumn() == 1) {
  $deleteAll = true;
}

if ($trial == "all" || $deleteAll) {
  try {
    $query = $db->prepare("DELETE FROM joinParents WHERE Hash = ? AND Tenant = ?");
    $query->execute([
      $hash,
      $tenant->getId()
    ]);
    $all = true;
  } catch (Exception $e) {
    halt(404);
  }
} else {
  try {
    $query = $db->prepare("DELETE FROM joinSwimmers WHERE Parent = ? AND ID = ? AND Tenant = ?");
    $query->execute([
      $hash,
      $trial,
      $tenant->getId()
    ]);
  } catch (Exception $e) {
    halt(404);
  }
}

if (isset($_REQUEST['redirect'])) {
  header("Location: " . $_REQUEST['redirect']);
  die();
}

$pagetitle = "Cancel Trial Request";
$use_white_background = true;
$use_website_menu = true;
if ($use_membership_menu) {
  $use_website_menu = false;
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Cancel Trial Request</h1>
  <div class="row">
    <div class="col-md-10 col-lg-8">
      <?php if ($all) { ?>
      <p class="lead mb-5">
        We've deleted all of your trial requests and deleted all of your
        personal information from our systems.
      </p>
      <p class="mb-5">
        <!-- <a href="" class="btn btn-lg btn-primary">
          Visit our website
        </a> -->
      </p>
      <?php } else { ?>
      <p class="lead mb-5">
        We've deleted that trial request.
      </p>
      <p>Return to your trial requests.</p>
      <p class="mb-5">
        <a href="<?=autoUrl($url_path . $hash)?>" class="btn btn-lg btn-primary">
          Return
        </a>
      </p>
      <?php } ?>
    </div>
  </div>
</div>


<?php

$footer = new \SCDS\Footer();
$footer->render();
