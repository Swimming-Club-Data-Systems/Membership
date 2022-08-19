<?php

if (isset($_SESSION['SCDS-SuperUser'])) {
  halt(404);
}

$pagetitle = "Login - Administration Dashboard";

include BASE_PATH . "views/root/head.php";

?>

<div class="container min-vh-100 mb-n3 overflow-auto">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">
      <div class="">
        <p class="mb-5">
          <a href="<?= htmlspecialchars(autoUrl('admin/login')) ?>" class="btn btn-outline-primary">Back</a>
          <a href="<?= htmlspecialchars(autoUrl('')) ?>" class="btn btn-outline-primary">Quit</a>
        </p>

        <h1 class="">Reset Password</h1>
        <p class="mb-5">Request a password reset for the admin dashboard</p>
      </div>

      <p>
        Self service password reset will become available at a later date.
      </p>

      <p>
        For now, please speak to your administrator to request a password reset for the SCDS Membership Admin Dashboard.
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>