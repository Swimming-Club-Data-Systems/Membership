<?php

$db = app()->db;

$pagetitle = "Database Migrations and Updates";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Database Migrations</h1>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();