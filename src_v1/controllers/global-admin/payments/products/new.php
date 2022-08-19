<?php

$pagetitle = "New Product - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments/products')) ?>">Products</a></li>
      <li class="breadcrumb-item active" aria-current="page">New</li>
    </ol>
  </nav>

  <h1>
    New Product
  </h1>
  <p class="lead">Add a new product.</p>

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['ProductAddError'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>There was a problem trying to create the new product</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['ProductAddError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['ProductAddError']);
      } ?>

      <form method="post" class="needs-validation" novalidate>

        <?= \SCDS\CSRF::write() ?>

        <div class="mb-3">
          <label class="form-label" for="product-name">Name</label>
          <input type="text" name="product-name" id="product-name" required class="form-control">
          <div class="invalid-feedback">
            Provide a name for this product
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="product-description">Description</label>
          <textarea name="product-description" id="product-description" rows="4" class="form-control"></textarea>
        </div>

        <p>
          <button type="submit" class="btn btn-primary">
            Add product
          </button>
        </p>
      </form>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->addJs('js/NeedsValidation.js');
$footer->render();
