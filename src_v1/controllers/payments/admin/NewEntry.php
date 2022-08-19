<?php

$pagetitle = "New Extra";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">
  <h1>New Extra</h1>
  <p class="lead">Add a new extra fee such as CrossFit.</p>

  <hr>

  <div class="row">
    <div class="col-lg-8">
      <form>
        <div class="mb-3">
          <label class="form-label" for="name">Extra Name</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Enter name">
        </div>

        <div class="mb-3">
          <label class="form-label" for="price">Price</label>
          <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon3">&pound;</span>
            <input type="text" class="form-control" id="price" name="price" placeholder="Enter price">
          </div>
        </div>

        <p>
          <button type="submit" class="btn btn-dark-l btn-outline-light-d">
            Add
          </button>
        </p>
      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>
