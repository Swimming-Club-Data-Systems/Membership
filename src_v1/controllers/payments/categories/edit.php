<?php

$tenant = app()->tenant;
$db = app()->db;

$get = $db->prepare("SELECT `Name`, `Description` FROM paymentCategories WHERE UniqueID = ? AND Tenant = ?");
$get->execute([
  $id,
  $tenant->getId(),
]);

$category = $get->fetch(PDO::FETCH_ASSOC);

if (!$category) {
  halt(404);
}

$pagetitle = htmlspecialchars($category['Name']) . ' - Payment Categories';

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?= autoUrl("payments/categories") ?>">Categories</a></li>
      <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
  </nav>

  <h1>
    <?= htmlspecialchars($category['Name']) ?>
  </h1>

  <div class="row">
    <div class="col-lg-8">
      <form method="post" class="needs-validation" novalidate>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewCategorySuccess'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>Success</strong>
            </p>
            <p class="mb-0">
              We've added the new category.
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewCategorySuccess']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SaveCategorySuccess'])) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>Success</strong>
            </p>
            <p class="mb-0">
              We've saved your changes to the category.
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['SaveCategorySuccess']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SaveCategoryError'])) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>Error</strong>
            </p>
            <p class="mb-0">
              <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['SaveCategoryError']) ?>
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['SaveCategoryError']);
        } ?>

        <div class="mb-3">
          <label class="form-label" for="category-name">Category name</label>
          <input class="form-control" type="text" name="category-name" id="category-name" required value="<?= htmlspecialchars($category['Name']) ?>">
          <div class="invalid-feedback">
            Please provide a name for this category.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="category-description">Category description</label>
          <input class="form-control" type="text" name="category-description" id="category-description" required value="<?= htmlspecialchars($category['Description']) ?>">
          <div class="invalid-feedback">
            Please provide a description for this category, such as what it is for.
          </div>
        </div>

        <?= \SCDS\CSRF::write() ?>

        <p>
          <button type="submit" class="btn btn-success">
            Save
          </button>
        </p>

        <p>
          <a href="<?= htmlspecialchars(autoUrl("payments/categories/$id/remove")) ?>" class="btn btn-danger" title="Stop showing this category for new payments">Remove category from lists</a>
        </p>

      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
