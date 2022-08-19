<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$db = app()->db;
$userDetails = $db->prepare("SELECT `EmailComms`, `MobileComms` FROM `users` WHERE `UserID` = ?");
$userDetails->execute([
  $user->getId(),
]);

$details = $userDetails->fetch(PDO::FETCH_ASSOC);

$getCategories = $db->prepare("SELECT `ID` `id`, `Name` `name`, `Description` `description` FROM `notifyCategories` WHERE `Tenant` = ? AND `Active` ORDER BY `Name` ASC;");
$getCategories->execute([
  $tenant->getId()
]);


$pagetitle = 'Communications Options - Onboarding';

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
        <h1 class="text-center">Communications Options</h1>

        <p class="lead mb-5 text-center">
          How do you want to hear from us?
        </p>

        <?php if (isset($_SESSION['FormError']) && $_SESSION['FormError']) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>An error occurred when we tried to save the changes</strong>
            </p>
          </div>
        <?php unset($_SESSION['FormError']);
        } ?>

        <form method="post" class="needs-validation" novalidate>

          <div class="card mb-3">
            <div class="card-header">
              Email
            </div>
            <div class="card-body pb-0">

              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="emailContactOK" aria-describedby="emailContactOKHelp" name="emailContactOK" <?php if ($details['EmailComms']) { ?>checked<?php } ?>>
                  <label class="form-check-label" for="emailContactOK">I would like to receive important news and messages from squad coaches by email</label>
                  <div><small id="emailContactOKHelp" class="form-text text-muted">You'll still receive emails relating to your account if you don't receive news</small></div>
                </div>
              </div>

              <?php if ($user->hasPermission('Parent')) { ?>
                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="PaymentComms" aria-describedby="PaymentCommsHelp" name="PaymentComms" <?php if (isSubscribed($user->getId(), 'Payments')) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="PaymentComms">Receive payment emails</label>
                    <div><small id="PaymentCommsHelp" class="form-text text-muted">If you opt out, you'll still receive emails required for regulatory purposes such as receipts or advance payment emails.</small></div>
                  </div>
                </div>
              <?php } ?>

              <?php if ($user->hasPermission('Admin')) { ?>
                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="NewMemberComms" aria-describedby="NewMemberCommsHelp" name="NewMemberComms" <?php if (isSubscribed($user->getId(), 'NewMember')) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="NewMemberComms">Receive new member emails</label>
                    <div><small id="NewMemberCommsHelp" class="form-text text-muted">Get notified when new members are added.</small></div>
                  </div>
                </div>
              <?php } ?>

              <?php while ($category = $getCategories->fetch(PDO::FETCH_OBJ)) { ?>
                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="<?= htmlspecialchars('email-category-' . $category->id) ?>" aria-describedby="<?= htmlspecialchars('help-email-category-' . $category->id) ?>" name="<?= htmlspecialchars('email-category-' . $category->id) ?>" <?php if (isAbsolutelySubscribed($user->getId(), $category->id)) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="<?= htmlspecialchars('email-category-' . $category->id) ?>"><?= htmlspecialchars($category->name) ?></label>
                    <div><small id="<?= htmlspecialchars('help-email-category-' . $category->id) ?>" class="form-text text-muted"><?= htmlspecialchars($category->description) ?></small></div>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              SMS
            </div>
            <div class="card-body pb-0">

              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="smsContactOK" aria-describedby="smsContactOKHelp" name="smsContactOK" <?php if ($details['MobileComms']) { ?>checked<?php } ?>>
                  <label class="form-check-label" for="smsContactOK">I would like to receive important text messages</label>
                  <div><small id="smsContactOKHelp" class="form-text text-muted">We'll still use this to contact you in an emergency. <?= htmlspecialchars(app()->tenant->getName()) ?> may not offer SMS services.</small></div>
                </div>
              </div>
            </div>
          </div>

          <p>
            <button type="submit" class="btn btn-success">Confirm</button>
          </p>

        </form>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('js/NeedsValidation.js');
$footer->render();

?>