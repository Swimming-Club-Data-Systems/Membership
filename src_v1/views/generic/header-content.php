<?php

use Respect\Validation\Exceptions\FloatValException;

$db = app()->db;
$tenant = app()->tenant;

$logos = $tenant->getKey('LOGO_DIR');

$clubLogoColour = 'text-white logo-text-shadow';
$navTextColour = 'navbar-dark';
$clubLinkColour = 'btn-light';

if (app()->tenant->getKey('SYSTEM_COLOUR') && getContrastColor(app()->tenant->getKey('SYSTEM_COLOUR'))) {
  $clubLogoColour = 'text-dark';
  $navTextColour = 'navbar-light';
  $clubLinkColour = 'btn-dark';
}

$bg = "";
if (isset($customBackground) && $customBackground) {
  $bg = $customBackground;
}

$container_class;
if (isset($fluidContainer) && $fluidContainer == true) {
  $container_class = "container-fluid";
} else {
  $container_class = "container-xl";
}
?>

  <div class="visually-hidden visually-hidden-focusable">
    <a href="#maincontent">Skip to main content</a>
  </div>

  <?php if (bool(getenv('IS_DEV'))) { ?>
    <div class="bg-warning text-dark bg-striped py-1 d-print-none">
      <div class="<?= $container_class ?>">
        <small><strong>DEVELOPMENT PLATFORM</strong> NOT FOR PRODUCTION USE</small>
      </div>
    </div>
  <?php } ?>

  <?php if (app()->tenant->getBooleanKey('PAYMENT_OVERDUE') && app()->user && app()->user->hasPermission('Admin')) { ?>
    <div class="bg-danger text-light text-light-d bg-striped py-1 d-print-none">
      <div class="<?= $container_class ?>">
        <small><strong>PAYMENT TO SCDS IS OVERDUE</strong> THIS TENANT MAY SOON BE SUSPENDED</small>
      </div>
    </div>
  <?php } ?>

  <?php if (app()->tenant->getBooleanKey('PAYMENT_METHOD_INVALID') && app()->user && app()->user->hasPermission('Admin')) { ?>
    <div class="bg-danger text-light text-light-d bg-striped py-1 d-print-none">
      <div class="<?= $container_class ?>">
        <small><strong>PAYMENT METHOD MISSING OR UNUSABLE</strong> CORRECT YOUR PAYMENT INFORMATION FOR SCDS AS SOON AS POSSIBLE. FAILURE TO DO SO MAY RESULT IN TENANT SUSPENSION.</small>
      </div>
    </div>
  <?php } ?>

  <div class="d-print-none">

    <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') != 'NONE' && app()->tenant->getKey('EMERGENCY_MESSAGE')) {
      $markdown = new ParsedownExtra();
    ?>

      <div class="top-alert <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'DANGER') { ?>top-alert-danger<?php } ?> <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'WARN') { ?>top-alert-warning<?php } ?> <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'SUCCESS') { ?>top-alert-success<?php } ?>">
        <div class="<?= $container_class ?>">
          <?php try { ?>
            <?= $markdown->text(app()->tenant->getKey('EMERGENCY_MESSAGE')) ?>
          <?php } catch (Exception $e) { ?>
            <p>An emergency message has been set but cannot be rendered.</p>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

    <noscript>
      <div class="bg-warning text-dark box-shadow py-3 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="h2">
            <strong>
              JavaScript is disabled or not supported
            </strong>
          </p>
          <p>
            It looks like you've got JavaScript disabled or your browser does
            not support it. JavaScript is essential for our website to function
            properly so we recommend you enable it or upgrade to a browser which
            supports it as soon as possible. <strong><a class="text-dark" href="https://browsehappy.com/" target="_blank">Upgrade your browser
                today <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.
          </p>
          <p class="mb-0">
            If JavaScript is not supported by your browser, <?= app()->tenant->getKey('CLUB_NAME') ?>
            recommends you <strong><a class="text-dark" href="https://www.firefox.com">install Firefox by
                Mozilla</a></strong>.
          </p>
        </div>
      </div>
    </noscript>

    <?php if ($_SESSION['Browser']['Name'] == "Internet Explorer") { ?>
      <div class="bg-warning text-dark py-3 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="h2">
            <strong>
              Internet Explorer is not supported
            </strong>
          </p>
          <p>
            It looks like you're using Internet Explorer which we no longer support so we recommend you upgrade to a new browser which we do support as soon as possible. <strong><a class="text-dark" href="http://browsehappy.com/" target="_blank">Upgrade your browser today <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.
          </p>
          <p class="mb-0">
            <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> recommends you <strong><a class="text-dark" href="https://www.firefox.com">install Firefox by Mozilla</a></strong>. Firefox has great protections for your privacy with built in features including tracking protection.
          </p>
        </div>
      </div>
    <?php } ?>

    <?php if (bool(getenv('IS_EVALUATION_COPY'))) { ?>
      <div class="bg-warning text-dark py-2 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="mb-0">
            <strong>
              This is an evaluation copy of this software
            </strong>
          </p>
          <p class="mb-0">
            Your club is testing this system
          </p>
        </div>
      </div>
    <?php } ?>

    <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation'])) { ?>
      <div class="bg-dark text-white py-2 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="mb-0">
            <strong>
              You are in user simulation mode simulating <?=
                                                          $_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation']['SimUserName'] ?>
            </strong>
          </p>
          <p class="mb-0">
            <a href="<?= htmlspecialchars(autoUrl("users/simulate/exit")) ?>" class="text-white">
              Exit User Simulation Mode
            </a>
          </p>
        </div>
      </div>
    <?php } ?>

    <?php
    $edit_link = null;
    if ((!isset($people) || !$people) && isset($allow_edit_id)) {
      $edit_link = autoUrl("posts/" . $allow_edit_id . "/edit");
    } else if (isset($people) && isset($page_is_mine) && $people && $page_is_mine) {
      $edit_link = autoUrl("people/me");
    }

    if (isset($allow_edit) && $allow_edit && (($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent" &&
      $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Coach" && $edit_link != null) || $page_is_mine)) { ?>
      <div class="bg-dark box-shadow py-2 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="mb-0">
            <a href="<?= htmlspecialchars($edit_link) ?>" class="text-white">
              Edit this page
            </a>
          </p>
        </div>
      </div>
    <?php } ?>

    <div class="membership-header">
      <div class="club-name-header <?php if (date("m") == "12") { ?>festive<?php } ?>" style="background-color: rgba(0, 0, 0, .075)">
        <div class="<?= $container_class ?>">
          <div class="row justify-content-between align-items-center py-3 mb-0 d-none d-md-flex">
            <div class="col-auto">
              <h1 class="mb-0">
                <a href="<?= htmlspecialchars(autoUrl("")) ?>" class="<?= $clubLogoColour ?> text-light-d text-decoration-none fw-bold">
                  <?php if ($tenant->getKey('LOGO_DIR') && $tenant->getKey('SHOW_LOGO')) { ?>
                    <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75.png')) ?>" srcset="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@2x.png')) ?> 2x, <?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@3x.png')) ?> 3x" alt="<?= htmlspecialchars($tenant->getName()) ?>" class="img-fluid" style="height: 75px">
                  <?php } else { ?>
                    <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>
                  <?php } ?>
                </a>
              </h1>
            </div>
            <?php if (app()->tenant->getKey('CLUB_WEBSITE')) { ?>
              <div class="col-auto">
                <a href="<?= htmlspecialchars(app()->tenant->getKey('CLUB_WEBSITE')) ?>" class="btn <?= $clubLinkColour ?> btn-outline-light-d text-decoration-none">Club website <i class="fa fa-external-link" aria-hidden="true"></i></a>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>

      <?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) || !user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) { ?>
        <div>
          <div class="<?= $container_class ?>">
            <div class="">
              <div class="">
                <nav class="navbar navbar-expand-lg <?= $navTextColour ?>
        d-print-none justify-content-between px-0" role="navigation">

                  <a class="navbar-brand align-items-center d-lg-none" href="<?= htmlspecialchars(autoUrl("")) ?>">
                    <?php if ($tenant->getKey('LOGO_DIR')) { ?>
                      <span class="d-md-none">
                        <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'icon-114x114.png')) ?>" alt="<?= htmlspecialchars($tenant->getName()) ?>" class="img-fluid rounded me-1 bg-white" style="height: 38px">
                      </span>
                    <?php } ?>

                    <span class="">
                      <?php if (app()->tenant->getKey('CLUB_SHORT_NAME')) { ?>
                        <?= htmlspecialchars(app()->tenant->getKey('CLUB_SHORT_NAME')) ?> Membership
                      <?php } else { ?>
                        <?= htmlspecialchars(app()->tenant->getKey('ASA_CLUB_CODE')) ?> Club Membership
                      <?php } ?>
                    </span>
                  </a>
                  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#chesterNavbar" aria-controls="chesterNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                  </button>

                  <?php include BASE_PATH . 'views/menus/main.php'; ?>
                </nav>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>

    </div>

    <div id="maincontent"></div>

    <!-- END OF HEADERS -->
    <div class="mb-3"></div>

  </div>

  <div class="d-none d-print-block">
    <?php
    $addr = json_decode(app()->tenant->getKey('CLUB_ADDRESS'));
    $logoPath = null;
    if ($logos = app()->tenant->getKey('LOGO_DIR')) {
      $logoPath = ($logos . 'logo-1024.png');
    }
    ?>

    <div class="container-xl">
      <div class="row mb-3">
        <div class="col club-logos">
          <?php if ($logoPath) { ?>
            <img src="<?= htmlspecialchars(getUploadedAssetUrl($logoPath)) ?>" class="">
          <?php } else { ?>
            <h1 class="primary"><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></h1>
          <?php } ?>
        </div>
        <div class="col text-end">
          <!-- <p class="mb-0"> -->
          <address>
            <strong><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></strong><br>
            <?php
            for ($i = 0; $i < sizeof($addr); $i++) { ?>
              <?= htmlspecialchars($addr[$i]) ?><br>
              <?php if (isset($addr[$i + 1]) && $addr[$i + 1] == "") {
                break;
              } ?>
            <?php } ?>
          </address>
          <!-- </p> -->
        </div>
      </div>
    </div>
  </div>