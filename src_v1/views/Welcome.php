<?php

$use_white_background = true;

include BASE_PATH . "views/header.php";

?>

<div class="container-xl">
  <div class="row">
    <div class="col-md-10 col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?>
        <p class="lead">By proceeding to use this progressive web app you agree to our use of cookies.</p>
      <?php } ?>

      <h1 class="mb-5">Welcome to the <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> Membership System</h1>

      <h2>Already registered?</h2>
      <p class="lead">
        Log in to your account now
      </p>
      <p class="mb-5">
        <a class="btn btn-lg btn-primary" href="<?= htmlspecialchars(autoUrl('login')) ?>">
          Login
        </a>
      </p>

      <h2>Not got an account?</h2>
      <p class="lead">
        Your club will create your account for you.
      </p>
      <p class="mb-5">
        If you've just joined, the person handling your application will be in touch soon.
      </p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
