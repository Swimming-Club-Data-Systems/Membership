<?php

$db = app()->db;
$tenant = app()->tenant;

$use_white_background = true;

$null = null;
if (isset($page)) {
  $null = $page;
}

if (isset($_GET['page'])) {
  $page = (int) $_GET['page'];
} else {
  $page = 1;
}

$start = 0;

if ($page != null) {
  $start = ($page - 1) * 10;
} else {
  $page = 1;
}

if ($page == 1 && $null != null) {
  header("Location: " . autoUrl("posts"));
  die();
}

$sql = "SELECT COUNT(*) FROM `posts` WHERE Tenant = ?";
try {
  $query = $db->prepare($sql);
  $query->execute([
    $tenant->getId()
  ]);
} catch (PDOException $e) {
  halt(500);
}
$numPosts = $query->fetchColumn();
$numPages = ((int)($numPosts / 10)) + 1;

if ($start > $numPosts) {
  halt(404);
}

$sql = "SELECT * FROM `posts` WHERE Tenant = :tenant ORDER BY `Title` ASC, `Date` DESC LIMIT :start, 10;";
try {
  $query = $db->prepare($sql);
  $tenantId = $tenant->getId();
  $query->bindParam('tenant', $tenantId, PDO::PARAM_INT);
  $query->bindParam('start', $start, PDO::PARAM_INT);
  $query->execute();
} catch (PDOException $e) {
  halt(500);
}
$row = $query->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Posts";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/postsMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>Pages</h1>
        <?php if ($row) { ?>
          <p class="lead mb-0">
            Page <?= $page ?> of <?= $numPages ?>
          </p>
        <?php } else { ?>
          <p class="lead mb-0">
            Write content for policies and more
          </p>
        <?php } ?>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <?php if (app()->user->hasPermission('Admin')) { ?>
        <div class="col-lg-auto ms-auto">
          <a href="<?= htmlspecialchars(autoUrl('pages/new')) ?>" class="btn btn-success">Add page</a>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<div class="container-xl">
  <main class="">
    <?php if ($row) { ?>
      <ul class="list-group">
        <?php do {
          $date = new DateTime($row['Date'], new DateTimeZone('UTC'));
          $date->setTimezone(new DateTimeZone('Europe/London'));
          $modified = new DateTime($row['Modified'], new DateTimeZone('UTC'));
          $modified->setTimezone(new DateTimeZone('Europe/London'));
          $id = $row['ID'];
        ?>
          <li class="list-group-item">
            <div class="row align-items-center">
              <div class="col">
                <?php
                $post_title = $row['Title'];
                $truncate = "";
                if (mb_strlen($post_title) == 0) {
                  $post_title = autoUrl($url);
                  $truncate = "text-truncate";
                } ?>
                <p class="mb-0 <?= $truncate ?>">
                  <?php
                  $url = "pages/" . $row['ID'];
                  if ($row['Path'] != null && mb_strlen($row['Path']) > 0) {
                    $url = "pages/" . $row['Path'];
                  }
                  ?>
                  <a href="<?= autoUrl($url) ?>">
                    <strong>
                      <?= htmlspecialchars($post_title) ?>
                    </strong>
                  </a>
                </p>
                <?php if ($row['Excerpt'] != "") { ?>
                  <p class="mb-0">
                    <?= htmlspecialchars($row['Excerpt']) ?>
                  </p>
                <?php } ?>
                <p class="mb-0">
                  First Published <?= htmlspecialchars($date->format('H:i, d F Y')) ?>,
                  Last Updated <?= htmlspecialchars($modified->format('H:i, d F Y')) ?>
                </p>
              </div>
              <?php if (app()->user->hasPermission('Admin')) { ?>
                <div class="col-auto">
                  <a href="<?= htmlspecialchars(autoUrl("pages/$id/edit")) ?>" class="btn btn-light">Edit</a>
                </div>
              <?php } ?>
            </div>
          </li>
        <?php } while ($row = $query->fetch(PDO::FETCH_ASSOC)); ?>
      </ul>
    <?php } else { ?>
      <div class="alert alert-warning">
        <p class="mb-0">
          <strong>There are no posts to display</strong>
        </p>
        <p class="mb-0">
          Add a post and it will show up here
        </p>
      </div>
    <?php } ?>

    <nav aria-label="Page navigation">
      <ul class="pagination">
        <?php if ($numPosts <= 10) { ?>
          <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
        <?php } else if ($numPosts <= 20) { ?>
          <?php if ($page == 1) { ?>
            <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page + 1 ?>">Next</a></li>
          <?php } else { ?>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page - 1 ?>">Previous</a></li>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page - 1 ?>"><?php echo $page - 1 ?></a></li>
            <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
          <?php } ?>
        <?php } else { ?>
          <?php if ($page == 1) { ?>
            <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page + 2 ?>"><?php echo $page + 2 ?></a></li>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page + 1 ?>">Next</a></li>
          <?php } else { ?>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page - 1 ?>">Previous</a></li>
            <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page - 1 ?>"><?php echo $page - 1 ?></a></li>
            <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
            <?php if ($numPosts > $page * 10) { ?>
              <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
              <li class="page-item"><a class="page-link" href="<?php echo autoUrl("posts?page="); ?><?php echo $page + 1 ?>">Next</a></li>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      </ul>
    </nav>
  </main>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
