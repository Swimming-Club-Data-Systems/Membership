<?php

$db = app()->db;

include 'head.php';

?>

<?php if (bool(getenv('IS_DEV'))) { ?>
  <aside class="bg-warning py-3 mb-3">
    <div class="container">
      <h1>
        Warning
      </h1>
      <p class="lead mb-0">
        This is development software which is <strong>not for production use</strong>
      </p>
    </div>
  </aside>
<?php } ?>

<div class="container">
  <div class="row align-items-center py-2">
    <div class="col-auto">
      <img src="<?= htmlspecialchars(autoUrl("img/corporate/scds.png")) ?>" class="img-fluid rounded-top" style="height: 75px;">
    </div>
    <div class="col-auto d-none d-md-flex">
      <h1 class="mb-0">
        <span class="sr-only">SCDS </span>Membership Software
      </h1>
    </div>
  </div>

  <nav class="navbar navbar-expand-md navbar-dark rounded-bottom rounded-right bg-primary">
    <a class="navbar-brand d-md-none" href="<?= htmlspecialchars(autoUrl("")) ?>">Membership Software</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
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
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Admin
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin")) ?>">Dashboard</a>
              <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin/notify")) ?>">Notify</a>
              <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("admin/register")) ?>">New Tenant</a>
              <!-- <div class="dropdown-divider"></div> -->
            </div>
          </li>
          <!-- <li class="nav-item">
            <a class="nav-link" href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a>
          </li> -->
        <?php } ?>
      </ul>
    </div>
  </nav>
</div>

<div id="maincontent"></div>

<!-- END OF HEADERS -->
<div class="mb-3"></div>

</div>

<div class="have-full-height">