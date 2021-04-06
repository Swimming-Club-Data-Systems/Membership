<?php



include BASE_PATH . "views/header.php";

?>

<div class="container">
  <div class="row">
    <div class="col-md-10 col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['PWA']) && $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['PWA']) { ?>
        <p class="lead">By proceeding to use this progressive web app you agree to our use of cookies.</p>
      <?php } ?>

      <h1 class="mb-5">Welcome to the <?= htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_NAME')) ?> Membership System</h1>

      <h2>Already registered?</h2>
      <p class="lead">
        Log in to your account now
      </p>
      <p class="mb-5">
        <a class="btn btn-lg btn-primary" href="<?= htmlspecialchars(autoUrl('login')) ?>">
          Login
        </a>
      </p>

      <h2>COVID-19 Contact Tracing</h2>
      <p class="lead">
        Check in online if you've been asked to fill out a contact tracing form
      </p>
      <p class="mb-5">
        <a class="btn btn-lg btn-primary" href="<?= htmlspecialchars(autoUrl('covid/contact-tracing')) ?>">
          Check In
        </a>
      </p>

      <h2>Not got an account?</h2>
      <p class="lead">
        Your club will create your account.
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
