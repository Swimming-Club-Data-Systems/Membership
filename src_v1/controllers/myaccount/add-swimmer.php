<?php

$fluidContainer = true;
$pagetitle = "Add a swimmer";
include BASE_PATH . "views/header.php";

$errorMessage = "";
$errorState = false;

$id = $acs = null;

if (isset($_GET['id'])) {
  $id = $_GET['id'];
}

if (isset($_GET['acs'])) {
  $acs = $_GET['acs'];
}

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") { ?>
  <div class="container-fluid">

    <div class="row justify-content-between">
      <div class="col-md-3 d-none d-md-block">
        <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
        echo $list->render('addswimmer');
        ?>
      </div>
      <div class="col-md-9">
        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AddSwimmerSuccessState'])) {
          echo $_SESSION['TENANT-' . app()->tenant->getId()]['AddSwimmerSuccessState'];
          unset($_SESSION['TENANT-' . app()->tenant->getId()]['AddSwimmerSuccessState']);
        } else if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
          echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
          unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
        } else { ?>
          <div class="">
            <h1>Add a member</h1>
            <p>We need a few details to find a member in our database.</p>
            <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
              echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
              unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
            } ?>
            <?php if ($id != null && $acs != null) { ?>
              <div class="alert alert-success">
                <p class="mb-0"><strong>Thanks for following that link</strong></p>
                <p class="mb-0">We've automatically filled in the required details for
                  you. <strong>Press Add Member</strong> to add the swimmer to your
                  account.</p>
              </div>
            <?php } ?>
            <form method="post" class="cell needs-validation" action="<?= htmlspecialchars(autoUrl("my-account/add-member")) ?>" name="register" id="register" novalidate>
              <h2>Details</h2>
              <div class="mb-3">
                <label class="form-label" for="asa">Member's Swim England Number or Temporary Membership Number</label>
                <input class="form-control mb-0" type="text" name="asa" id="asa" placeholder="123456" required value="<?= htmlspecialchars($id) ?>">
                <div class="invalid-feedback">
                  A Swim England or Member Number is required
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="accessKey">Access Key</label>
                <input class="form-control mb-0 font-monospace" type="text" name="accessKey" id="accessKey" placeholder="1A3B5C" required value="<?= htmlspecialchars($acs) ?>">
                <div class="invalid-feedback">
                  An access key is required
                </div>
              </div>

              <button type="submit" class="btn btn-success">
                Add member
              </button>
            </form>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
<?php }

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->useFluidContainer();
$footer->render(); ?>