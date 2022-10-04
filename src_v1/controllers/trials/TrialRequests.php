<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Tenant = ?");
$query->execute([
  $tenant->getId()
]);

$count = $query->fetchColumn();

$query = $db->prepare("SELECT ID, Hash, Email, joinSwimmers.First, joinSwimmers.Last, joinParents.First PFirst, joinParents.Last PLast FROM joinParents INNER JOIN joinSwimmers ON joinParents.Hash = joinSwimmers.Parent WHERE joinParents.Tenant = ? AND SquadSuggestion IS NULL ORDER BY ID DESC");
$query->execute([
  $tenant->getId()
]);

$pagetitle = "Trial Requests";
$use_white_background = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Trial Requests</h1>
  <div class="row">
    <div class="col-md-10 col-lg-8">
      <p class="lead">
        Trial Requests by Parents.
      </p>

      <?php if ($count == 0) { ?>
      <div class="alert alert-warning">
        <strong>There are no current trial requests</strong>
      </div>
      <?php } ?>

      <?php
      while ($parent = $query->fetch(PDO::FETCH_ASSOC)) { ?>
      <div class="cell">
        <h2><?=htmlspecialchars($parent['First'] . ' ' . $parent['Last'])?></h2>
        <p>
          Contact <?=htmlspecialchars($parent['PFirst'] . ' ' . $parent['PLast'])?> via email at <a href="mailto:<?=htmlspecialchars($parent['Email'])?>"><?=htmlspecialchars($parent['Email'])?></a>
        </p>

        <div class="row">
          <div class="col-md">
            <a href="<?=autoUrl("trials/" . $parent['ID'])?>" class="btn btn-block btn-dark-l btn-outline-light-d">
              Trial Info
            </a>
          </div>

          <div class="col mb-2 d-md-none">
          </div>

          <div class="col-md">
            <a href="<?=autoUrl("trials/" . $parent['ID'] . "/recommendations")?>" class="btn btn-block btn-dark-l btn-outline-light-d">
              Suggest Squad
            </a>
          </div>

          <div class="col mb-2 d-md-none">
          </div>

          <div class="col-md">
            <div class="dropdown">
              <button class="btn btn-block btn-danger dropdown-toggle" type="button" id="deleteDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Advanced
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="deleteDropdown">
                <a class="dropdown-item" href="<?=autoUrl($url_path . $parent['Hash'] . "/cancel/" . $parent['ID'])?>?redirect=<?=urlencode(autoUrl("trials"))?>">Cancel Trial Request</a>
              </div>
            </div>
          </div>
        </div>

      </div>
      <?php }
      ?>

    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
