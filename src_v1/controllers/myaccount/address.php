<?php

$fluidContainer = true;

$db = app()->db;
$currentUser = app()->user;

$address = null;
$json = $currentUser->getUserOption('MAIN_ADDRESS');
if ($json != null) {
  $address = json_decode($json);
}

$pagetitle = "My Address";
include BASE_PATH . "views/header.php";
  $userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
?>
<div class="container-fluid">
  <div class="row justify-content-between">
    <div class="col-md-3 d-none d-md-block">
      <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
        echo $list->render('postal-address');
      ?>
    </div>
    <div class="col-md-9">
      <h1>My Address</h1>
      <p class="lead">Add or edit an address</p>

    	<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']) && $_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']) { ?>
    		<div class="alert alert-success">
    			<p class="mb-0">
    				<strong>We've successfully updated your address</strong>
    			</p>
    		</div>
    	<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']);
    	} ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']) && !$_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']) { ?>
    		<div class="alert alert-danger">
    			<p class="mb-0">
    				<strong>Please check your details and try again</strong>
    			</p>
    		</div>
    	<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']);
    	} ?>

      <?php if (isset($address->streetAndNumber)) { ?>
      <h2>Your address is</h2>
      <address>
        <?=htmlspecialchars($address->streetAndNumber)?><br>
        <?php if (isset($address->flatOrBuilding)) { ?>
        <?=htmlspecialchars($address->flatOrBuilding)?><br>
        <?php } ?>
        <?=htmlspecialchars($address->city)?><br>
        <?php if (isset($address->county)) { ?>
        <?=htmlspecialchars($address->county)?><br>
        <?php } ?>
        <?=htmlspecialchars(mb_strtoupper($address->postCode))?><br>
      </address>

      <h2>Edit address</h2>
      <?php } else { ?>
      <h2>Add address</h2>
      <?php } ?>

      <p>You must use a UK address or a British Forces address.</p>

    	<form method="post" class="needs-validation" novalidate>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label" for="street-and-number">Address line 1 (street and number)</label>
              <input class="form-control" name="street-and-number" id="street-and-number" type="text" autocomplete="address-line1" <?php if (isset($address->streetAndNumber)) { ?>value="<?=htmlspecialchars($address->streetAndNumber)?>"<?php } ?> required>
              <div class="invalid-feedback">
                Please enter your street and number.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="flat-building">Address line 2 (optional)</label>
              <input class="form-control" name="flat-building" id="flat-building" type="text" autocomplete="address-line2" <?php if (isset($address->flatOrBuilding)) { ?>value="<?=htmlspecialchars($address->flatOrBuilding)?>"<?php } ?>>
            </div>

            <div class="mb-3">
              <label class="form-label" for="town-city">Town/City</label>
              <input class="form-control" name="town-city" id="town-city" type="text" autocomplete="address-level2" <?php if (isset($address->city)) { ?>value="<?=htmlspecialchars($address->city)?>"<?php } ?> required>
              <div class="invalid-feedback">
                Please enter your town or city.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="county-province">County</label>
              <input class="form-control" name="county-province" id="county-province" type="text" autocomplete="address-level1" <?php if (isset($address->county)) { ?>value="<?=htmlspecialchars($address->county)?>"<?php } ?> required>
              <div class="invalid-feedback">
                Please enter your county (historic or ceremonial).
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="post-code">Post Code</label>
              <input class="form-control" name="post-code" id="post-code" type="text" autocomplete="postal-code" <?php if (isset($address->postCode)) { ?>value="<?=htmlspecialchars($address->postCode)?>"<?php } ?> required pattern="[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]?[\s]{0,1}[0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}">
              <div class="invalid-feedback">
                Please enter a valid post code.
              </div>
            </div>
          </div>
        </div>

    		<p class="mb-0">
    			<button type="submit" class="btn btn-success">Update details</button>
    		</p>
    	</form>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->useFluidContainer();
$footer->render(); ?>
