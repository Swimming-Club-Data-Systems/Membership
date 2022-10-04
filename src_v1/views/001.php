<?php

$trace = debug_backtrace();

http_response_code(404);
$pagetitle = "This page has moved";
$currentUser = null;
if (isset(app()->user)) {
  $currentUser = app()->user;
}
if ($currentUser == null && false) {
  $clubLogoColour = 'text-white logo-text-shadow';
  $navTextColour = 'navbar-dark';
  $clubLinkColour = 'btn-light';

  if (app()->tenant->getKey('SYSTEM_COLOUR') && getContrastColor(app()->tenant->getKey('SYSTEM_COLOUR'))) {
    $clubLogoColour = 'text-dark';
    $navTextColour = 'navbar-light';
    $clubLinkColour = 'btn-dark';
  }

  include BASE_PATH . "views/head.php";

?>
  <div class="py-3 mb-3 text-white membership-header <?= $clubLogoColour ?>">
    <div class="container-xl">
      <h1 class="mb-0">
        <a href="<?= autoUrl("") ?>" class="<?= $clubLogoColour ?>">
          <strong>
            <?= mb_strtoupper(htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))) ?>
          </strong>
        </a>
      </h1>
    </div>
  </div>
<?php
} else {
  include BASE_PATH . "views/header.php";
}
?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>This page has moved to the new version of the membership system</h1>

      <p class="lead">
        Please try to access this page from SCDS Next (V2).
      </p>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>