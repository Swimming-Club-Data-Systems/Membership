<?php

$db = app()->db;
$tenant = app()->tenant;

$pagetitle = "Categories";

$categories = $db->prepare("SELECT `UniqueID`, `Name` FROM paymentCategories WHERE `Tenant` = ? AND `Show` ORDER BY `Name` ASC");
$categories->execute([
  $tenant->getId(),
]);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">

  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
        <li class="breadcrumb-item active" aria-current="page">Categories</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Categories
        </h1>
        <p class="lead mb-0">
          Create and manage categories for payments
        </p>
        <div class="mb-3 d-lg-none"></div>
      </div>
      <div class="col text-end">
        <p class="mb-0">
          <a href="<?= htmlspecialchars(autoUrl("payments/categories/new")) ?>" class="btn btn-success">New category</a>
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['HideCategorySuccess'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>Category hidden successfully</strong>
          </p>
          <p class="mb-0">
            <em><?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['HideCategorySuccess']) ?></em> will no longer be shown as an option for future payments.
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['HideCategorySuccess']);
      } ?>

      <?php if ($category = $categories->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="list-group">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl("payments/categories/" . $category['UniqueID'])) ?>" class="list-group-item list-group-item-action">
              <?= htmlspecialchars($category['Name']) ?>
            </a>
          <?php } while ($category = $categories->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>Your organisation has no existing categories</strong>
          </p>
          <p class="mb-0">
            <a href="<?= htmlspecialchars(autoUrl("payments/categories/new")) ?>" class="alert-link">Add one</a> to get started.
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
