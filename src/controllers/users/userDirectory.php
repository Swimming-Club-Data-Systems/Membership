<?php

$use_white_background = true;

$search = "";
parse_str($_SERVER['QUERY_STRING'], $queries);
if (isset($queries['search'])) {
  $search = $queries['search'];
}
$pagetitle = "Users";
include BASE_PATH . "views/header.php";
?>
<div class="mt-3"></div>
<div class="container">
  <h1>User Directory</h1>
  <p class="lead">A list of users. Useful for changing account settings.</p>
  <div class="form-group row">
    <label class="col-sm-4 col-md-3 col-lg-2" for="search">Search by Name</label>
    <div class="col-sm-8 col-md-9 col-lg-10">
      <input class="form-control" id="search" name="search" value="<?=htmlspecialchars($search)?>">
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

<script src="<?=autoUrl("js/users/list.js")?>"></script>

<?php include BASE_PATH . "views/footer.php";
