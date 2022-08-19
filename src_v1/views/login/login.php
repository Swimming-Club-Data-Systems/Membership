<?php

$db = app()->db;

$selectedClub = 'SCDS';
$club = null;
if (isset($_GET['club']) && mb_strlen((string) $_GET['club']) > 0) {
  $club = Tenant::fromCode((string) $_GET['club']);
  if (!$club) {
    $club = Tenant::fromId((int) $_GET['club']);
  }
}

if (!$club) {
  http_response_code(303);
  header("location: " . autoUrl("clubs"));
}

$colour = null;
if ($club) {
  $colour = $club->getKey('SYSTEM_COLOUR');
  $selectedClub = $club->getName();
}

$target = "";
if (isset($_GET['target'])) {
  $target = $_GET['target'];
}

$pagetitle = "Sign in to " . htmlspecialchars($selectedClub) . " Membership";

include BASE_PATH . "views/root/head.php";

?>

<div class="bg-dark">
  <div <?php if ($colour) { ?>style="background: <?= htmlspecialchars($colour) ?>" <?php } ?>>
    <div style="background: rgba(255, 255, 255, .5)">

      <div class="container-fluid">
        <div class="row justify-content-center min-vh-100 align-items-center py-4">
          <div class="col-12" style="max-width: 500px;">
            <div class="card card-body text-dark mb-0" id="central-card">
              <div class="row align-items-center mb-3">
                <div class="col-auto">
                  <a href="<?= htmlspecialchars(autoUrl("", false)) ?>">
                  <img src="<?= htmlspecialchars(autoUrl("img/corporate/scds.png")) ?>" class="img-fluid rounded" style="height: 75px;">
                  </a>
                </div>
                <div class="col-auto">
                  <h1 class="d-flex">
                    Login
                  </h1>
                </div>
              </div>
              <p class="lead" id="login-box-text">
                Sign in with your <?= htmlspecialchars($selectedClub) ?> account.
              </p>

              <form action="<?= htmlspecialchars(autoUrl($club->getCodeId() . "/login", false)) ?>" method="post" id="login-form" class="needs-validation" novalidate data-prefilled="<?= htmlspecialchars((int) isset($_GET['user'])) ?>" data-ajax-login-url="<?= htmlspecialchars(autoUrl("login", false)) ?>" data-ajax-two-factor-url="<?= htmlspecialchars(autoUrl("login/2fa", false)) ?>">
                <div class="mb-3">
                  <label class="form-label" for="email-address">Email address</label>
                  <input type="email" class="form-control form-control-lg text-lowercase" id="email-address" name="email-address" placeholder="yourname@example.com" required autocomplete="email" <?php if (isset($_GET['user'])) { ?>value="<?= htmlspecialchars(urldecode($_GET['user'])) ?>" <?php } else { ?> autofocus <?php } ?>>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="password">Password</label>
                  <input type="password" name="password" id="password" class="form-control form-control-lg" required placeholder="Password" autocomplete="current-password" <?php if (isset($_GET['user'])) { ?> autofocus <?php } ?>>
                </div>

                <input type="hidden" name="target" value="<?= htmlspecialchars($target) ?>">

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember-me" id="remember-me" checked aria-describedby="remember-me-help">
                    <label class="form-check-label" for="remember-me">Keep me logged in</label>
                    <small id="remember-me-help" class="form-text text-muted">
                      Untick this box if you are using a public or shared computer
                    </small>
                  </div>
                </div>

                <?= \SCDS\CSRF::write() ?>

                <p class="mb-0">
                  <button type="submit" class="btn btn-primary btn-lg" id="submit" disabled>
                    Sign in
                  </button>

                  <a class="btn btn-light btn-lg" id="cancel" href="<?= htmlspecialchars(autoUrl("", false)) ?>">
                    Cancel
                  </a>
                </p>
              </form>

              <hr>

              <p>
                Forgotten your account details?
              </p>

              <p class="mb-0">
                <a class="btn btn-light" id="find-account" href="<?= htmlspecialchars(autoUrl($club->getCodeId() . "/resetpassword", false)) ?>">
                  Find account
                </a>
              </p>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <?php $footer = new \SCDS\RootFooter();
  $footer->addJs('public/js/login/login.js');
  $footer->chrome(false);
  $footer->render(); ?>