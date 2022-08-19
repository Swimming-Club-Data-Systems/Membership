<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$search = null;

if (isset($_GET['search'])) {
	$search = trim($_GET['search']);
}

$use_white_background = true;
$pagetitle = "Find a Parent's Current Fees";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">
	<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments")?>">Payments</a></li>
			<li class="breadcrumb-item"><a href="<?=autoUrl("payments/history")?>">History &amp; Status</a></li>
      <li class="breadcrumb-item active" aria-current="page">Find a parent</li>
    </ol>
  </nav>
	<div>
		<h1>Find a parent's transaction history</h1>
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
        window.history.replaceState("string", "Title", "<?=autoUrl("payments/history/users")?>?search=" + searchValue);
      }
    }
    xhttp.open("POST", "<?php echo autoURL("payments/current/ajax"); ?>", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("target=transactionhistory&search=" + searchValue);
    console.log("Sent");
}
// Call getResult immediately
getResult();

document.getElementById("search").oninput=getResult;
</script>

<?php

$footer = new \SCDS\Footer();
$footer->render();
