<?php if (!isset($renewal_trap) || !$renewal_trap) {
$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']; ?>

<!--
<div class="bg-light" style="margin:-1rem 0 1rem 0;">
  <div class="<?=$container_class?>">
    <nav class="navbar navbar-expand-lg navbar-light px-0">
      <button class="btn btn-primary d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        Payments Menu
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="<?=autoUrl("payments")?>">Payments Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?=autoUrl("payments/history")?>">Status</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?=autoUrl("payments/extrafees")?>">Monthly Extras</a>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</div>
-->

<?php } else {
  include 'renewalTitleBar.php';
}
