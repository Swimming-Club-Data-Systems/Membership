<?php

$db = app()->db;

$getProducts = $db->query("SELECT `ID`, `Name`, `Description`, `Updated` FROM `tenantPaymentProducts` ORDER BY `Name` ASC;");
$product = $getProducts->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Products - Payments - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('admin/payments')) ?>">Pay</a></li>
      <li class="breadcrumb-item active" aria-current="page">Products</li>
    </ol>
  </nav>

  <h1>
    Products
  </h1>
  <p class="lead">Automatic subscription and billing systems.</p>

  <p>
    <a class="btn btn-primary" href="<?= htmlspecialchars(autoUrl('admin/payments/products/new')) ?>">New Product</a>
  </p>

  <?php if ($product) { ?>
    <div class="list-group">
      <?php do { ?>
        <a href="<?= htmlspecialchars(autoUrl('admin/payments/products/' . $product['ID'])) ?>" class="list-group-item list-group-item-action">
          <p class="mb-0">
            <strong><?= htmlspecialchars($product['Name']) ?></strong>
          </p>
          <?php if ($product['Description']) { ?>
            <p class="mb-0">
              <?= htmlspecialchars($product['Description']) ?>
            </p>
          <?php } ?>
        </a>
      <?php } while ($product = $getProducts->fetch(PDO::FETCH_ASSOC)); ?>
    </div>
  <?php } else { ?>
    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>No products available</strong>
      </p>
      <p class="mb-0">
        Please <a class="alert-link" href="<?= htmlspecialchars(autoUrl('admin/payments/products/new')) ?>">add a product</a>.
      </p>
    </div>
  <?php } ?>


</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
