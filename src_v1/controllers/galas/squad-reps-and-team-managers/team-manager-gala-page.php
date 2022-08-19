<?php

/**
 * TEAM MANAGER HOME PAGE FOR A GALA
 */

\SCDS\Can::view('TeamManager', $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id);

$db = app()->db;
$tenant = app()->tenant;

$galaInfo = $db->prepare("SELECT GalaName FROM galas WHERE GalaID = ? AND Tenant = ?");
$galaInfo->execute([
  $id,
  $tenant->getId()
]);
$gala = $galaInfo->fetch(PDO::FETCH_ASSOC);

$pagetitle = htmlspecialchars($gala['GalaName']) . " - Team Managers";

include BASE_PATH . 'views/header.php';

?>

<div class="front-page mb-n3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=autoUrl("galas")?>">Galas</a></li>
        <li class="breadcrumb-item"><a href="<?=autoUrl("galas/" . $id)?>">This Gala</a></li>
        <li class="breadcrumb-item active" aria-current="page">TM Dashboard</li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-md-8">
        <h1><?=htmlspecialchars($gala['GalaName'])?><br><small>Team Managers</small></h1>
        <p class="lead">Welcome to the team manager dashboard for <?=htmlspecialchars($gala['GalaName'])?>.</p>
        <p>Team manager features are slowly being introduced and you'll eventually be able to see gala entries, medical information, emergency contacts, photography permissions and take registers for each session at a gala.</p>
      </div>
    </div>

    <div class="news-grid mb-4">
      <a href="<?=autoUrl("galas/" . $id . "/team-manager-view")?>">
        <span class="mb-3">
          <span class="title mb-0">
            View entries
          </span>
          <span>
            View all entries for this gala
          </span>
        </span>
        <span class="category">
          Galas
        </span>
      </a>

      <a href="<?=autoUrl("galas/" . $id . "/swimmers")?>">
        <span class="mb-3">
          <span class="title mb-0">
            View swimmer details
          </span>
          <span>
            View essential medical and emergency contact details
          </span>
        </span>
        <span class="category">
          Swimmers
        </span>
      </a>

      <a href="<?=autoUrl("galas/" . $id . "/photography-permissions.pdf")?>">
        <span class="mb-3">
          <span class="title mb-0">
            View photography permissions
          </span>
          <span>
            Check what you can do with photos of swimmers
          </span>
        </span>
        <span class="category">
          Swimmers
        </span>
      </a>

      <!--
      <a href="<?=autoUrl("galas/" . $id . "/registers")?>">
        <span class="mb-3">
          <span class="title mb-0">
            Take a register
          </span>
          <span>
            Take a register to record swimmer attendance at each session
          </span>
        </span>
        <span class="category">
          Attendance
        </span>
      </a>
      -->
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();