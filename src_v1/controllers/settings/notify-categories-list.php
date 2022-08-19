<?php

$db = app()->db;
$tenant = app()->tenant;

$getCategories = $db->prepare("SELECT `ID` `id`, `Name` `name`, `Description` `description` FROM `notifyCategories` WHERE `Tenant` = ? AND `Active` ORDER BY `Name` ASC;");
$getCategories->execute([
  $tenant->getId()
]);
$category = $getCategories->fetch(PDO::FETCH_OBJ);

ob_clean();
ob_start();

?>

<?php if ($category) { ?>

  <ul class="list-group">
    <?php do { ?>
      <li class="list-group-item">
        <form class="needs-validation" novalidate id="<?= htmlspecialchars($category->id) ?>-cat-form">
          <h2>
            <?= htmlspecialchars($category->name) ?>
          </h2>

          <input type="hidden" name="category" value="<?= htmlspecialchars($category->id) ?>">

          <div class="mb-3">
            <label for="name" class="form-label">Category name</label>
            <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($category->name) ?>">
            <div class="invalid-feedback">You must provide a category name</div>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Category description</label>
            <textarea rows="3" class="form-control" id="description" name="description" required><?= htmlspecialchars($category->description) ?></textarea>
            <div class="invalid-feedback">You must provide a textual description of this category</div>
          </div>

          <div class="row">
            <div class="col-auto">
              <button type="submit" class="btn btn-success">Save</button>
            </div>
            <div class="col-auto ms-auto">
              <button type="button" class="btn btn-danger" data-action="delete" data-category-id="<?= htmlspecialchars($category->id) ?>">Delete category</button>
            </div>
          </div>
        </form>
      </li>
    <?php } while ($category = $getCategories->fetch(PDO::FETCH_OBJ)); ?>
  </ul>

<?php } else { ?>

  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>There are no custom categories to display</strong>
    </p>
  </div>

<?php } ?>

<?php

$html = ob_get_clean();

header('content-type: application/json');
echo json_encode([
  'listHtml' => $html,
]);
