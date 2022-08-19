<?php

$db = app()->db;

require 'GlobalHead.php';

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['AlphaBeta'])) {
  if (rand() < 0.5) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['AlphaBeta'] = false;
  } else {
    $_SESSION['TENANT-' . app()->tenant->getId()]['AlphaBeta'] = true;
  }
}

$bg = "bg-white";
if (isset($customBackground) && $customBackground) {
  $bg = $customBackground;
}
?>

<?php if (false /*$_SESSION['TENANT-' . app()->tenant->getId()]['AlphaBeta']*/) { ?>
  <style>
    h1 {
      background: #bd0000;
      padding: 1rem;
      color: #fff;
      width: max-content;
      margin: 3rem 0 0 0;
    }

    p.lead {
      background: #bd0000;
      padding: 1rem;
      color: #fff;
      width: max-content;
      margin: 0 0 3rem 0;
    }
  </style>
<?php } ?>

<body class="<?= $bg ?> account--body <?php if (isset($pageHead['body-class'])) {
                                        foreach ($pageHead['body-class'] as $item) { ?> <?= $item ?> <?php }
                                                                                                  } ?>" <?php if (isset($pageHead['body'])) {
                                                                                                        foreach ($pageHead['body'] as $item) { ?> <?= $item ?> <?php }
                                                                                                                                                                                                    } ?>>

  <?php if (bool(getenv('IS_DEV'))) { ?>
    <div class="bg-warning bg-striped py-1">
      <div class="<?= $container_class ?>">
        <small><strong>DEVELOPMENT PLATFORM</strong> NOT FOR PRODUCTION USE</small>
      </div>
    </div>
  <?php } ?>

  <div class="visually-hidden visually-hidden-focusable">
    <a href="#maincontent">Skip to main content</a>
  </div>

  <div class="d-print-none">

    <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') != 'NONE' && app()->tenant->getKey('EMERGENCY_MESSAGE')) {
      $markdown = new ParsedownExtra();
    ?>
      <style>
        .text-white .emergency-message a {
          color: #fff;
          font-weight: bold;
          text-decoration: underline;
        }

        .text-body .emergency-message a {
          color: #212529;
          font-weight: bold;
          text-decoration: underline;
        }

        .text-white .emergency-message thead {
          color: #212529;
        }

        .emergency-message p:last-child {
          margin-bottom: 0 !important;
          padding-bottom: 0 !important;
        }
      </style>

      <div class="py-3 <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'DANGER') { ?>bg-danger text-white<?php } ?> <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'WARN') { ?>bg-warning text-body<?php } ?> <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'SUCCESS') { ?>bg-success text-white<?php } ?>">
        <div class="<?= $container_class ?> emergency-message">
          <?php try { ?>
            <?= $markdown->text(app()->tenant->getKey('EMERGENCY_MESSAGE')) ?>
          <?php } catch (Exception $e) { ?>
            <p>An emergency message has been set but cannot be rendered.</p>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

    <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation'])) { ?>
      <div class="bg-secondary text-white box-shadow py-2 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="mb-0">
            <strong>
              You are in User Simulation Mode simulating <?=
                                                          $_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation']['SimUserName'] ?>
            </strong>
          </p>
          <p class="mb-0">
            <a href="<?= autoUrl("users/simulate/exit") ?>" class="text-white">
              Exit User Simulation Mode
            </a>
          </p>
        </div>
      </div>
    <?php } ?>

    <noscript>
      <div class="bg-warning box-shadow py-3 d-print-none">
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
            supports it as soon as possible. <strong><a class="text-dark" href="http://browsehappy.com/" target="_blank">Upgrade your browser
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

    <?php if (isset($_SESSION['Browser']['OSName']) && $_SESSION['Browser']['OSName'] == "Android" && isset($_SESSION['Browser']['OSVersion']) && (float) $_SESSION['Browser']['OSVersion'] <= 7.1) { ?>
      <div class="bg-warning text-dark py-3 d-print-none small">
        <div class="<?= $container_class ?>">
          <p class="h2">
            <strong>
              Your device will not be supported from February 2021
            </strong>
          </p>
          <p>
            It looks like you're using Android version <?= htmlspecialchars($_SESSION['Browser']['OSVersion']) ?>. From February, you won't be able to access this site on your device because the DST Root X3 certificate will expire.
          </p>
          <p class="mb-0">
            Upgrade to at least Android 7.1.1 now or <strong><a class="text-dark" href="https://www.firefox.com">install Firefox by Mozilla</a></strong>. Firefox uses it's own root certificate list which avoids this problem and has great protections for your privacy with built in features including tracking protection.
          </p>
        </div>
      </div>
    <?php } ?>

    <?php if ($_SESSION['Browser']['Name'] == "Internet Explorer") { ?>
      <div class="bg-warning py-3 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="h2">
            <strong>
              Internet Explorer is not supported
            </strong>
          </p>
          <p>
            It looks like you're using Internet Explorer which we no longer support so we recommend you upgrade to a new
            browser which we do support as soon as possible. <strong><a class="text-dark" href="http://browsehappy.com/" target="_blank">Upgrade your browser today <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.
          </p>
          <p class="mb-0">
            <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> recommends you <strong><a class="text-dark" href="https://www.firefox.com">install
                Firefox by Mozilla</a></strong>. Firefox has great protections for your privacy with built in features
            including tracking protection.
          </p>
        </div>
      </div>
    <?php } ?>

    <?php
    $edit_link = null;
    if (isset($allow_edit_id)) {
      $edit_link = autoUrl("posts/" . $allow_edit_id . "/edit");
    }

    if (isset($allow_edit) && $allow_edit && (($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent" &&
      $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Coach" && $edit_link != null) || $page_is_mine)) { ?>
      <div class="bg-dark text-white box-shadow py-2 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="mb-0">
            <a href="<?= $edit_link ?>" class="text-white">
              Edit this page
            </a>
          </p>
        </div>
      </div>
    <?php } ?>

    <div class="text-white py-2 top-bar bg-primary-dark hide-a-underline" style="font-size:0.875rem;">
      <div class="<?= $container_class ?> d-flex">
        <div class="me-auto">
          <span class="me-2">
            <a href="https://www.twitter.com/CLSASC" target="_blank" class="text-white" title="Twitter">
              <i class="fa fa-twitter fa-fw" aria-hidden="true"></i>
              <span class="visually-hidden">Chester-le-Street ASC on Twitter</span>
            </a>
          </span>

          <span class="me-2">
            <a href="https://www.facebook.com/CLSASC" target="_blank" class="text-white" title="Facebook">
              <i class="fa fa-facebook fa-fw" aria-hidden="true"></i>
              <span class="visually-hidden">Chester-le-Street ASC on Facebook</span>
            </a>
          </span>
        </div>

        <span class="d-flex" id="top-bar-visible">
        </span>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && $_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) { ?>
          <span class="d-none" id="top-bar-login-status">1</span>
        <?php } else { ?>
          <span class="d-none" id="top-bar-login-status">0</span>
        <?php } ?>

        <div class="ms-2 top-bar d-lg-none">
          <span>
            <a data-bs-toggle="collapse" href="#mobSearch" role="button" aria-expanded="false" aria-controls="mobSearch" class="text-white" title="Search the site">
              Search
            </a>
          </span>
        </div>

        <div class="ms-2 top-bar">
          <span>
            <a id="top-bar-more-link" href="#top-bar-more" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="top-bar-more" class="text-white d-none" title="More Links">
              More <i class="fa fa-caret-down" aria-hidden="true"></i>
            </a>
          </span>
        </div>
      </div>
    </div>

    <div class="collapse " id="top-bar-more">
      <div class="bg-primary-dark py-2 border-top border-white hide-a-underline" style="font-size:0.875rem;">
        <div class="<?= $container_class ?>">
          <div id="top-bar-more-content">
          </div>
        </div>
      </div>
    </div>

    <div class="collapse" id="mobSearch">
      <div class="text-white py-3 d-lg-none bg-primary-darker">
        <form class="container-xl" action="https://www.chesterlestreetasc.co.uk" id="head-search" method="get">
          <label class="form-label" for="s" class="visually-hidden">Search</label>
          <div class="input-group">
            <input class="form-control bg-primary text-white border-primary" id="s" name="s" placeholder="Search the site" type="search">
            <button type="submit" class="btn btn-primary">
              <i class="fa fa-search"></i>
              <span class="visually-hidden">
                Search
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="text-white py-3 d-none d-lg-flex bg-primary-darker <?php if (date("m") == "12") { ?>festive<?php } ?>">
      <div class="<?= $container_class ?>">
        <div class="row align-items-center">
          <div class="col">
            <a class="logowhite" href="<?= autoUrl("") ?>" title="Membership Dashboard"></a>
          </div>
          <div class="col d-none d-lg-flex">
            <p class="lead mb-0 ms-auto text-end">Club Membership</p>
          </div>
        </div>
      </div>
    </div>

    <?php if (true || !isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?>
      <div class="bg-primary <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) && user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) { ?>d-lg-none<?php } ?>">
        <div class="<?= $container_class ?>">
        <?php } ?>
        <nav class="navbar <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?><?php } ?>  navbar-expand-lg navbar-dark bg-primary
    d-print-none justify-content-between px-0" <?php if (isset($use_website_menu) && $use_website_menu) { ?>id="club-menu" <?php } ?> role="navigation" style="font-size: .8rem;">

          <a class="navbar-brand d-lg-none" href="<?= autoUrl("") ?>">
            <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) { ?>
              <img src="<?php echo autoUrl("img/chesterIcon.svg", false); ?>" width="20" height="20"> My Membership
            <?php } else { ?>
              <img src="<?php echo autoUrl("img/chesterIcon.svg", false); ?>" width="20" height="20"> Club Membership
            <?php } ?>
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#chesterNavbar" aria-controls="chesterNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <?php include BASE_PATH . 'views/menus/main.php'; ?>

          <?php if (true || !isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?>
        </div>

      </div>
    <?php } ?>

    <div id="maincontent"></div>

    <!-- END OF HEADERS -->
    <div class="mb-3"></div>
  </div>


  <?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?>
    <div class="have-full-height focus-highlight" style="min-height:70vh">
    <?php } else { ?>
      <div class="have-full-height focus-highlight">
      <?php } ?>