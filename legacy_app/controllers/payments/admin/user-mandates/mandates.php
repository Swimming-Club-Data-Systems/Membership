<?php

$pagetitle = "User Mandates";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>User mandates</h1>
      <p class="lead">
        View mandates for a user
      </p>

      <p>
        To view a user's mandates, please <a href="<?=htmlspecialchars(autoUrl("users"))?>">navigate to the user</a> and select <strong>Mandates</strong>.
      </p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();