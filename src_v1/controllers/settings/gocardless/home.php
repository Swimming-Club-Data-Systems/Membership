<?php

$fluidContainer = true;
$pagetitle = 'Direct Debit';

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-gc');
      ?>
    </aside>
    <div class="col-md-9">
      <main>

        <h1>
          Direct Debit settings
        </h1>
        <p class="lead">
          Manage your GoCardless connection
        </p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Reg-Success'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>We've connected your GoCardless Account</strong>
            </p>
            <p class="mb-0">
              See x about getting started
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Reg-Success']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Reg-Error'])) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>We were unable to connect your GoCardless Account</strong>
            </p>
            <p class="mb-0">
              <a href="<?= htmlspecialchars(autoUrl("settings/direct-debit/register")) ?>" class="alert-link">Try again now</a> or try again later.
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Reg-Error']);
        } ?>

        <?php if ($at = app()->tenant->getGoCardlessAccessToken()) { ?>

          <p>
            Your GoCardless account is currently connected.
          </p>

          <p>
            You can revoke our access to your account by <a href="https://manage.gocardless.com/">signing in to GoCardless</a>.
          </p>

        <?php } else { ?>

          <p>
            Direct Debit is deeply integrated into the membership system.
          </p>

          <p>
            <a href="<?= htmlspecialchars(autoUrl("settings/direct-debit/register")) ?>" class="btn btn-primary">Get started</a>
          </p>

          <p>
            We'll send you to GoCardless and ask you to sign in or create an account. Find out <a href="https://gocardless.com/#features" target="_blank">more about GoCardless</a> and <a href="https://gocardless.com/pricing/" target="_blank">their pricing</a>.
          </p>

        <?php } ?>

      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
