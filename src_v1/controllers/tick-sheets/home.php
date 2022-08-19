<?php

$db = app()->db;
$tenant = app()->tenant;

$pagetitle = "Tick Sheets";

$markdown = new ParsedownExtra();

include BASE_PATH . 'views/header.php';

?>

<!-- Temporarily render schema -->
<style>
  table {
    /* border: 1px solid; */
    width: 100%;
    margin-bottom: 1rem;
    border-color: #555;
  }

  tr {
    border-top: 1px solid;
    border-color: #555;
  }

  thead>tr {
    border-bottom: 5px solid;
    border-color: #555;
    border-top: none;
  }

  th,
  td {
    padding: 0.25rem;
  }
</style>

<div class="bg-light mt-n3 py-3 mb-3">

  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Tick Sheets</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Tick Sheets
        </h1>
        <p class="lead mb-0">
          Create and manage tick sheets
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <!-- <div class="col text-end">
        <p class="mb-0">
          <a href="<?= htmlspecialchars(autoUrl("tick-sheets/new")) ?>" class="btn btn-success">New category</a>
        </p>
      </div> -->
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="alert alert-warning">
    Under development
  </div>

  <div class="cell">
    <?= $markdown->text(file_get_contents(BASE_PATH . 'controllers/tick-sheets/tick-sheet-schema.md')) ?>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
