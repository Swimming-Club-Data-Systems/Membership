<?php

$fluidContainer = true;

$pagetitle = "Notify Categories";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('notify-categories');
      ?>
    </aside>
    <div class="col-md-9">
      <main>
        <h1>Notify Categories</h1>

        <p class="lead">
          Ensure compliance with data protection law with custom subscription categories for emails.
        </p>

        <p>
          Users must opt-in to any new category you create, as per the GDPR. In appropriate circumstances, you may use Force Send.
        </p>

        <p>
          <button class="btn btn-success" id="new-button">New</button>
        </p>

        <div id="category-section"></div>
      </main>
    </div>
  </div>
</div>

<div id="js-data" data-list-ajax-url="<?= htmlspecialchars(autoUrl('settings/notify-categories/list')) ?>" data-add-ajax-url="<?= htmlspecialchars(autoUrl('settings/notify-categories/new')) ?>" data-delete-ajax-url="<?= htmlspecialchars(autoUrl('settings/notify-categories/delete')) ?>" data-update-ajax-url="<?= htmlspecialchars(autoUrl('settings/notify-categories/update')) ?>"></div>

<!-- Modal for use by JS code -->
<div class="modal fade" id="main-modal" tabindex="-1" role="dialog" aria-labelledby="main-modal-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="main-modal-title">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body" id="main-modal-body">
        ...
      </div>
      <div class="modal-footer" id="main-modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="modal-confirm-button" class="btn btn-success">Confirm</button>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->addJs('js/settings/notify-categories.js');
$footer->render();
