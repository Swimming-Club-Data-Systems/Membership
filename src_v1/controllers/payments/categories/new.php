<?php

$db = app()->db;

$pagetitle = "New Category";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?= autoUrl("payments/categories") ?>">Categories</a></li>
      <li class="breadcrumb-item active" aria-current="page">New</li>
    </ol>
  </nav>

  <h1>
    Add a new payment category
  </h1>

  <div class="row">
    <div class="col-lg-8">
      <form method="post" class="needs-validation" novalidate>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NewCategoryError'])) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>Error</strong>
            </p>
            <p class="mb-0">
              <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['NewCategoryError']) ?>
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['NewCategoryError']);
        } ?>

        <div class="mb-3">
          <label class="form-label" for="category-name">Category name</label>
          <input class="form-control" type="text" name="category-name" id="category-name" required>
          <div class="invalid-feedback">
            Please provide a name for this category.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="category-description">Category description</label>
          <input class="form-control" type="text" name="category-description" id="category-description" required>
          <div class="invalid-feedback">
            Please provide a description for this category, such as what it is for.
          </div>
        </div>

        <?= \SCDS\CSRF::write() ?>

        <p>
          <button type="submit" class="btn btn-success">
            Add new
          </button>
        </p>

      </form>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
