<?php

$pagetitle = "Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <div class="row justify-content-center py-3">
    <div class="col-lg-8 col-md-10">
    <div class="bg-primary text-white p-4 mb-4 d-inline-block rounded">
      <h1 class="">Admin tools</h1>
      <p class="mb-0">Select a utility.</p>
      </div>

      <div class="list-group">
        <a href="<?= htmlspecialchars(autoUrl('admin/notify/history')) ?>" class="list-group-item list-group-item-action">
          Notify Usage
        </a>
        <a href="<?= htmlspecialchars(autoUrl('admin/audit')) ?>" class="list-group-item list-group-item-action">
          Audit Logs
        </a>
        <a href="<?= htmlspecialchars(autoUrl('admin/users')) ?>" class="list-group-item list-group-item-action">
          User Search
        </a>
        <a href="<?= htmlspecialchars(autoUrl('admin/tenants')) ?>" class="list-group-item list-group-item-action">
          Tenants
        </a>
        <a href="<?= htmlspecialchars(autoUrl('admin/register')) ?>" class="list-group-item list-group-item-action">
          Add Tenant
        </a>
      </div>

      
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>