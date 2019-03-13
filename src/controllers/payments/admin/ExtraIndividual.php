<?php

$id = mysqli_real_escape_string($link, $id);
$user = $_SESSION['UserID'];

$sql = "SELECT * FROM `extras` WHERE `ExtraID` = '$id';";
$result = mysqli_query($link, $sql);

if (mysqli_num_rows($result) != 1) {
  halt(404);
}

$sql = "SELECT * FROM `squads` ORDER BY `SquadFee` DESC, `SquadName` ASC;";
$squads = mysqli_query($link, $sql);

$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

$pagetitle = $row['ExtraName'] . " - Extras";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

 ?>

<div class="container">
  <div class="row align-items-center">
    <div class="col-md-6">
	    <h1><?php echo $row['ExtraName']; ?></h1>
    </div>
    <div class="col text-right">
      <a href="<?php echo autoUrl("payments/extrafees/" . $id . "/edit"); ?>"
        class="btn btn-dark">Edit</a>
      <a href="<?php echo autoUrl("payments/extrafees/" . $id . "/delete"); ?>"
        class="btn btn-danger">Delete</a>
    </div>
  </div>
  <hr>
  <div class="row">
    <div class="col-md-6">
      <div id="output">
        <div class="ajaxPlaceholder">
          <span class="h1 d-block">
            <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i>
            <br>Loading Content
          </span>If content does not display, please turn on JavaScript
        </div>
      </div>
    </div>
    <div class="col">
      <div class="my-3 p-3 bg-white rounded shadow">
        <form>
          <div class="form-group">
            <label for="squadSelect">Select Squad</label>
            <select class="custom-select" id="squadSelect" name="squadSelect">
              <option selected>Choose...</option>
              <?php for ($i = 0; $i < mysqli_num_rows($squads); $i ++) {
                $squadsRow = mysqli_fetch_array($squads, MYSQLI_ASSOC); ?>
              <option value="<?php echo $squadsRow['SquadID']; ?>">
                <?php echo $squadsRow['SquadName']; ?>
              </option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label for="swimmerSelect">Select Swimmer</label>
            <select class="custom-select" id="swimmerSelect" name="swimmerSelect">
              <option selected>Select squad first</option>
            </select>
          </div>
            <button type="button" class="btn btn-dark" id="addSwimmer">
              Add Swimmer to Extra
            </button>
            <div id="status">
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function getSwimmers() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        console.log("We got here");
        document.getElementById("output").innerHTML = this.responseText;
        console.log(this.responseText);
      }
    }
    xhttp.open("POST", "<?php echo autoUrl("payments/extrafees/ajax/" . $id); ?>", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("response=getSwimmers");
    console.log("Sent");
}

function getSwimmersForSquad() {
  var squad = (document.getElementById("squadSelect")).value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        console.log("We got here");
        document.getElementById("swimmerSelect").innerHTML = this.responseText;
        console.log(this.responseText);
      }
    }
    xhttp.open("POST", "<?php echo autoUrl("payments/extrafees/ajax/" . $id); ?>", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("response=squadSelect&squadSelect=" + squad);
    console.log("Sent");
}

function addSwimmerToExtra() {
  var swimmer = (document.getElementById("swimmerSelect")).value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        getSwimmers();
        document.getElementById("squadSelect").innerHTML = "<option selected>Choose...</option>";
        document.getElementById("swimmerSelect").innerHTML = "<option selected>Select squad first</option>";
        document.getElementById("status").innerHTML =
        '<div class="mt-3 mb-0 alert alert-success alert-dismissible fade show" role="alert">' +
        '<strong>Successfully Added Swimmer</strong>'  +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>';
      } else {
        document.getElementById("status").innerHTML =
        '<div class="mt-3 mb-0 alert alert-warning alert-dismissible fade show" role="alert">' +
        '<strong>Unable to add swimmer</strong>' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>';
      }
    }
    xhttp.open("POST", "<?php echo autoUrl("payments/extrafees/ajax/" . $id); ?>", true);
    console.log("POST", "<?php echo autoUrl("payments/extrafees/ajax/" . $id); ?>", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("response=insert&swimmerInsert=" + swimmer);
    console.log("response=insert&swimmerInsert=" + swimmer);
    console.log("Sent");
}

function dropSwimmerFromExtra(relation) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      getSwimmers();
    }
  }
  xhttp.open("POST", "<?php echo autoUrl("payments/extrafees/ajax/" . $id); ?>", true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("response=dropRelation&relation=" + relation);
}

var entryTable = document.querySelector("#output");
entryTable.addEventListener("click", clickPropogation, false);

function clickPropogation(e) {
    if (e.target !== e.currentTarget) {
        var clickedItem = e.target.id;
        var clickedItemValue;
        if (clickedItem != "") {
          var clickedItemValue = document.getElementById(clickedItem).value;
          dropSwimmerFromExtra(clickedItemValue);
        }
    }
    e.stopPropagation();
}

// Call getResult immediately
getSwimmers();
document.getElementById("squadSelect").onchange=getSwimmersForSquad;
document.getElementById("addSwimmer").onclick=addSwimmerToExtra;
</script>

<?php include BASE_PATH . "views/footer.php";
