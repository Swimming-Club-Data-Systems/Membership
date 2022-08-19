<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$search = null;

if (isset($_GET['search'])) {
	$search = trim($_GET['search']);
}

$pagetitle = "Find a Parent's Current Fees";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">
	<div class="">
		<h1 class="">Find a parent's current fees.</h1>
		<div class="mb-3">
	    <label class="visually-hidden" for="search">Search by Surname</label>
			<div class="input-group">
				<span class="input-group-text">Search</span>
	    	<input class="form-control" placeholder="Surname" id="search" name="search" value="<?=htmlspecialchars($search)?>">
			</div>
	  </div>
		<div id="output">
	    <div class="ajaxPlaceholder">
	      <span class="h1 d-block">
	        <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i><br>
	        Loading Content
	      </span>
	      If content does not display, please turn on JavaScript
	    </div>
	  </div>
	</div>
</div>

<script>
function getResult() {
  var search = document.getElementById("search");
  var searchValue = search.value;
  console.log(searchValue);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        console.log("We got here");
        document.getElementById("output").innerHTML = this.responseText;
        console.log(this.responseText);
        window.history.replaceState("string", "Title", "<?php echo autoUrl("payments/current"); ?>?search=" + searchValue);
      }
    }
    xhttp.open("POST", "<?php echo autoURL("payments/current/ajax"); ?>", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("target=currentfees&search=" + searchValue);
    console.log("Sent");
}
// Call getResult immediately
getResult();

document.getElementById("search").oninput=getResult;
</script>

<?php

$footer = new \SCDS\Footer();
$footer->render();
