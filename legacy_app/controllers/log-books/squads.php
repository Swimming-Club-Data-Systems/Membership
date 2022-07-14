<?php

$db = app()->db;
$tenant = app()->tenant;

$getSquads = $db->prepare("SELECT SquadName squad, SquadID id FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, squad ASC");
$getSquads->execute([
  $tenant->getId()
]);
$squad = $getSquads->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Member log books";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <h1>Log books <span class="badge bg-info">BETA</span></h1>
  <p class="lead">
    Select a squad to view member's log books
  </p>

  <div class="row">
    <div class="col-md-8">
      <?php if ($squad) { ?>
      <div class="list-group mb-3">
        <?php do { ?>
        <a href="<?=htmlspecialchars(autoUrl("log-books/squads/" . $squad['id']))?>" class="list-group-item list-group-item-action">
          <p class="mb-0">
            <strong><?=htmlspecialchars($squad['squad'])?></strong>
          </p>
        </a>
        <?php } while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)); ?>
      </div>
      <?php } else { ?>
      <div class="alert alert-warning">
        <p class="mb-0">
          <strong>There are no squads to display</strong>
        </p>
      </div>
      <?php } ?>
    </div>
    <div class="col">
      <div class="position-sticky top-3">
        <div class="cell">
          <h2>Log books are new!</h2>
          <p class="lead">
            We have added log books to the membership system as a response to the coronavirus (COVID-19) outbreak.
          </p>
          <p>
            This is to allow members to log their home based land training.
          </p>
          <p class="mb-0">
            As always, feedback is very welcome. Send it to <a href="mailto:feedback@myswimmingclub.uk">feedback@myswimmingclub.uk</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();