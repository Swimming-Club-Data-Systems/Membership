<?php

$db = app()->db;
$tenant = app()->tenant;

$squads = $db->prepare("SELECT SquadName name, SquadID id FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$squads->execute([
  $tenant->getId()
]);

$fluidContainer = true;
$squadID = $search = "";
parse_str($_SERVER['QUERY_STRING'], $queries);
if (isset($queries['squadID'])) {
  $squadID = (int) $queries['squadID'];
}
if (isset($queries['search'])) {
  $search = $queries['search'];
}

$pagetitle = "Members";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php";

if (isset($_POST['squad'])) {
  $squadID = $_POST['squad'];
} ?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-fluid">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Members</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Members
        </h1>
        <p class="lead mb-0">
          View all members or view members by squad.
        </p>

        <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
          <p class="mt-3 mb-0">
            <a href="<?= autoUrl("members/new") ?>" class="btn btn-success">
              Add new member
            </a>
          </p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">

  <div class="d-print-none">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label" for="squad">Select a squad</label>
        <select class="form-select" placeholder="Select a Squad" id="squad" name="squad">
          <option value="allSquads">Show All Squads</option>;
          <?php while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
            <option value="<?= $squad['id'] ?>" <?php if ($squad['id'] == $squadID) { ?>selected<?php } ?>>
              <?= htmlspecialchars($squad['name']) ?>
            </option>
          <?php } ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="search">Search by name</label>
        <input class="form-control" placeholder="Search by name" id="search" name="search" value="<?= htmlspecialchars($search) ?>">
      </div>
    </div>
  </div>

  <div id="output">
    <div class="ajaxPlaceholder">
      <span class="h1 d-block">
        <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i>
        <br>Loading Content
      </span>If content does not display, please turn on JavaScript
    </div>
  </div>
</div>

<script>
  function getResult() {
    var squad = document.getElementById("squad");
    var squadValue = squad.options[squad.selectedIndex].value;
    var search = document.getElementById("search");
    var searchValue = search.value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("output").innerHTML = this.responseText;
        window.history.replaceState("string", "Title", "<?= autoUrl("members") ?>?squadID=" + squadValue + "&search=" + searchValue);
      }
    }
    xhttp.open("POST", "<?= autoUrl("members/ajax/swimmerDirectory") ?>", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("squadID=" + squadValue + "&search=" + searchValue);
  }
  // Call getResult immediately
  getResult();

  document.getElementById("squad").onchange = getResult;
  document.getElementById("search").oninput = getResult;
</script>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
