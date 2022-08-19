<?php

$fluidContainer = true;

$db = app()->db;

$page = null;
if (isset($_GET['page'])) {
  $page = $_GET['page'];
}

$null = $page;

$start = 0;

if ($page != null) {
  $start = ((int) $page-1)*10;
} else {
  $page = 1;
}

if ($page == 1 && $null != null) {
  header("Location: " . autoUrl("my-account/login-history"));
  die();
}

$sql = "SELECT `ID` FROM `userLogins` WHERE `UserID` = ?";
try {
	$query = $db->prepare($sql);
	$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} catch (PDOException $e) {
	halt(500);
}
$numLogins = sizeof($query->fetchAll(PDO::FETCH_ASSOC));
$numPages = ((int)($numLogins/10)) + 1;

if ($start > $numLogins) {
  halt(404);
}

$sql = "SELECT `Time`, `IPAddress`, `GeoLocation`, `Browser`, `Platform`, `Mobile` FROM `userLogins` WHERE `UserID` = :user ORDER BY `Time` DESC LIMIT :start, 10";
try {
	$query = $db->prepare($sql);
  $query->bindParam('user', $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], PDO::PARAM_INT);
  $query->bindParam('start', $start, PDO::PARAM_INT);
	$query->execute();
} catch (PDOException $e) {
	//  halt(500);
}

$row = $query->fetchAll(PDO::FETCH_ASSOC);

$pagetitle = "Login History";

include BASE_PATH . "views/header.php";
//include BASE_PATH . "views/notifyMenu.php";?>

<div class="container-fluid">

  <div class="row justify-content-between">
    <div class="col-md-3 d-none d-md-block">
      <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
        echo $list->render('logins');
      ?>
    </div>
    <div class="col-md-9">
      <h1>Your Login History</h1>
  		<div class="alert alert-danger">
  			<p class="mb-0"><strong>Spotted anything suspicious?</strong></p>
  			<p class="mb-0"><a href="<?=autoUrl("my-account/password")?>"
  			class="alert-link">Change your password</a> straight away.</p>
  		</div>
      <?php if ($numLogins == 0) { ?>
        <p class="lead pb-3 mb-0 border-bottom border-gray">
          You have never logged in.
        </p>
      <?php } else { ?>
      <p class="lead pb-3 mb-0 border-bottom border-gray">
        Page <?php echo $page; ?> of <?php echo $numPages; ?>
      </p>
      <?php for ($i = 0; $i < sizeof($row); $i++) {
      $date = new DateTime($row[$i]['Time'], new DateTimeZone('UTC'));
      $date->setTimezone(new DateTimeZone('Europe/London')); ?>
      <div class="media py-3 my-0 border-bottom border-gray">
        <div class="media-body my-0">
          <div class="d-block text-gray-dark">
            <p class="mb-0">
              <strong>
                Login at <?= $date->format('H:i \o\\n l j F Y') ?>
                using <?= htmlentities($row[$i]['Browser']) ?>
              </strong>
            </p>
            <p class="mb-0">
              <?php if ($row[$i]['Mobile']) { ?>
              Login from a mobile device running <?= htmlentities($row[$i]['Platform']) ?>
              <?php } else { ?>
              Login from a desktop computer running <?= htmlentities($row[$i]['Platform']) ?><?php } ?><?php if ($row[$i]['GeoLocation']) { ?> located in <?= htmlentities($row[$i]['GeoLocation']) ?><?php } ?>.
              IP Address: <?= htmlentities($row[$i]['IPAddress']) ?>
            </p>
          </div>
        </div>
      </div>
      <?php } ?>

      <nav aria-label="Page navigation">
        <ul class="pagination mt-3 mb-0">
          <?php if ($numLogins <= 10) { ?>
          <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
          <?php } else if ($numLogins <= 20) { ?>
            <?php if ($page == 1) { ?>
            <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
      			<li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page+1 ?>"><?php echo $page+1 ?></a></li>
      			<li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page+1 ?>">Next</a></li>
            <?php } else { ?>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page-1 ?>">Previous</a></li>
      	    <li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page-1 ?>"><?php echo $page-1 ?></a></li>
      	    <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
            <?php } ?>
          <?php } else { ?>
      			<?php if ($page == 1) { ?>
      			<li class="page-item active"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
      	    <li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page+1 ?>"><?php echo $page+1 ?></a></li>
      			<li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page+2 ?>"><?php echo $page+2 ?></a></li>
      			<li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page+1 ?>">Next</a></li>
            <?php } else { ?>
      			<li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page-1 ?>">Previous</a></li>
            <?php if ($page > 2) { ?>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page-2 ?>"><?php echo $page-2 ?></a></li>
            <?php } ?>
      	    <li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page-1 ?>"><?php echo $page-1 ?></a></li>
      	    <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
      			<?php if ($numLogins > $page*10) { ?>
      	    <li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page+1 ?>"><?php echo $page+1 ?></a></li>
            <?php if ($numLogins > $page*10+10) { ?>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page+2 ?>"><?php echo $page+2 ?></a></li>
            <?php } ?>
      	    <li class="page-item"><a class="page-link" href="<?php echo autoUrl("my-account/login-history?page="); ?><?php echo $page+1 ?>">Next</a></li>
            <?php } ?>
          <?php } ?>
        <?php } ?>
        </ul>
      </nav>
    <?php } ?>
    </div>
  </div>

</div>

<?php
$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
