<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$db = app()->db;

$contacts = new EmergencyContacts($db);
$contacts->byParent($user->getId());
$contactsArray = $contacts->getContacts();

$pagetitle = 'Emergency Contacts - Onboarding';

include BASE_PATH . "views/head.php";

?>

<div class="min-vh-100 mb-n3 overflow-auto">
  <div class="bg-light">
    <div class="container">
      <div class="row justify-content-center py-5">
        <div class="col-lg-8 col-md-10">

          <?php if ($logos) { ?>
            <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75.png')) ?>" srcset="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@2x.png')) ?> 2x, <?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@3x.png')) ?> 3x" alt="" class="img-fluid d-block mx-auto">
          <?php } else { ?>
            <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/scds.png')) ?>" height="75" width="75" alt="" class="img-fluid d-block mx-auto">
          <?php } ?>

        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row justify-content-center py-5">
      <div class="col-lg-8 col-md-10">
        <h1 class="text-center">Emergency Contacts</h1>

        <p class="lead mb-5 text-center">
          Add, edit or remove emergency contacts.
        </p>

        <?php if (isset($_SESSION['SaveSuccess']) && $_SESSION['SaveSuccess']) { ?>
          <div class="alert alert-success">
            <p class="mb-0">
              <strong>We saved your changes</strong>
            </p>
          </div>
        <?php unset($_SESSION['SaveSuccess']);
        } ?>

        <p class="text-center">
          We'll use these emergency contacts for all members connected to your accounts if we can't reach you on your own phone number. You can change your phone number later in the <em>My Account</em> section.
        </p>

        <div id="contact-box"></div>

        <p>
          <button class="btn btn-success" type="button" id="add-new-button">
            Add new emergency contact
          </button>
        </p>

        <p>
          Please let people know if you have assigned them as your emergency contacts.
        </p>

        <form method="post" class="needs-validation <?php if (sizeof($contactsArray) < 1) { ?>d-none<?php } ?>" novalidate id="continue-form">

          <p>
            <button type="submit" class="btn btn-success">Confirm</button>
          </p>

        </form>
      </div>
    </div>
  </div>
</div>

<div id="ajax-data" data-contacts-list-url="<?= htmlspecialchars(autoUrl('onboarding/go/emergency-contacts/list')) ?>" data-new-url="<?= htmlspecialchars(autoUrl('onboarding/go/emergency-contacts/new')) ?>" data-edit-url="<?= htmlspecialchars(autoUrl('onboarding/go/emergency-contacts/edit')) ?>" data-delete-url="<?= htmlspecialchars(autoUrl('onboarding/go/emergency-contacts/delete')) ?>"></div>

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
$footer->addJs('js/onboarding/client/emergency-contacts.js');
$footer->render();

?>