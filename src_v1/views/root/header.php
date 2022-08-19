<?php

$db = app()->db;

include 'head.php';

?>

</div>

<?php if (bool(getenv('IS_DEV'))) { ?>
  <aside class="bg-warning text-dark py-3 mb-3">
    <div class="container-xl">
      <h1>
        Warning
      </h1>
      <p class="lead mb-0">
        This is development software which is <strong>not for production use</strong>
      </p>

      <hr>

      <p>
        <a class="btn btn-outline-dark" href="https://myswimmingclub.uk">Find your club's homepage</a>
      </p>
    </div>
  </aside>
<?php } ?>

<div class="container-xl">
  <div class="row align-items-center py-2">
    <div class="col-auto">
      <img src="<?= htmlspecialchars(autoUrl("img/corporate/scds.png")) ?>" class="img-fluid rounded-top" style="height: 75px;">
    </div>
    <div class="col-auto d-none d-md-flex">
      <h1 class="visually-hidden">
        SCDS Membership Software
      </h1>
    </div>
  </div>

  <nav class="navbar navbar-expand-md navbar-dark rounded-bottom rounded-end bg-primary">
    <div class="container-xl">
      <a class="navbar-brand d-md-none" href="<?= htmlspecialchars(autoUrl("")) ?>">Membership Software</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="<?= htmlspecialchars(autoUrl("")) ?>">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= htmlspecialchars(autoUrl("clubs")) ?>">Clubs</a>
          </li>
          <!-- <li class="nav-item">
          <a class="nav-link" href="<?= htmlspecialchars(autoUrl("register")) ?>">Register</a>
        </li> -->
          <li class="nav-item">
            <a class="nav-link" href="<?= htmlspecialchars(autoUrl("help-and-support")) ?>">Help</a>
          </li>
          <?php if (isset($_SESSION['SCDS-SuperUser'])) { ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Admin
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin")) ?>">Dashboard</a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin/notify")) ?>">Notify</a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin/users")) ?>">User Search</a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin/audit")) ?>">Audit Logs</a>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin/payments")) ?>">Payments</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="https://outlook.com/owa/myswimmingclub.uk" target="_blank">Outlook</a>
                <a class="dropdown-item" href="https://login.microsoftonline.com/login.srf?wa=wsignin1%2E0&rver=6%2E1%2E6206%2E0&wreply=https%3A%2F%2Fmyswimmingclubuk-my.sharepoint.com%2F&whr=myswimmingclub.uk" target="_blank">OneDrive</a>
                <a class="dropdown-item" href="https://administration.myswimmingclub.uk/phpmyadmin" target="_blank">phpMyAdmin</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin/register")) ?>">New Tenant</a>
              </div>
            </li>
            <!-- <li class="nav-item">
            <a class="nav-link" href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a>
          </li> -->
          <?php } ?>
        </ul>
      </div>
    </div>
  </nav>
</div>

<div id="maincontent"></div>

<!-- END OF HEADERS -->
<div class="mb-3"></div>

</div>

<div class="have-full-height">