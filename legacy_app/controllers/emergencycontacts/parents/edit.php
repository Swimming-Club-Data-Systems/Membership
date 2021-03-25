<?php

$db = nezamy_app()->db;

$url_path = "emergency-contacts";
if (isset($renewal_trap) && $renewal_trap) {
	$url_path = "renewal/emergencycontacts";
}

$user = $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['UserID'];

$contact = new EmergencyContact();
$contact->connect($db);
$contact->getByContactID($id);

if ($contact->getUserID() != $user) {
	halt(404);
}

$pagetitle = htmlspecialchars($contact->getName()) . " - Emergency Contacts";

include BASE_PATH . 'views/header.php';
if (isset($renewal_trap) && $renewal_trap) {
	include BASE_PATH . 'views/renewalTitleBar.php';
}

?>

<div class="container">

  <?php if (!isset($renewal_trap) || !$renewal_trap) { ?>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("emergency-contacts")?>">Emergency Contacts</a></li>
      <li class="breadcrumb-item active" aria-current="page">Edit <?=htmlspecialchars($contact->getName())?></li>
    </ol>
  </nav>
  <?php } ?>

  <div class="">
    <h1>
      Edit <?=htmlspecialchars($contact->getName())?>
    </h1>

    <div class="row">
      <div class="col-lg-8">

        <?php if (isset($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['PhoneError']) && $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['PhoneError']) { ?>
          <div class="alert alert-danger">
            <p class="mb-0"><strong>
              The number provided was not a valid UK phone number.
            </strong></p>
            <p class="mb-0">
              Please try again
            </p>
          </div>
        <?php unset($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['PhoneError']); } ?>

        <form method="post" class="needs-validation" novalidate>
          <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="<?=htmlspecialchars($contact->getName())?>" required>
            <div class="invalid-feedback">
              You must provide the name of the emergency contact
            </div>
          </div>

          <div class="form-group">
            <label for="relation">Relation</label>
            <input type="text" class="form-control" id="relation" name="relation" placeholder="Relation" value="<?=htmlspecialchars($contact->getRelation())?>" required>
            <div class="invalid-feedback">
              You must provide the relation so we can decide who is best to call
            </div>
          </div>

          <div class="form-group">
            <label for="num">Contact Number</label>
            <input type="tel" pattern="\+{0,1}[0-9]*" class="form-control" id="num" name="num" placeholder="Phone" value="<?=htmlspecialchars($contact->getContactNumber())?>" required>
            <div class="invalid-feedback">
              You must provide a valid UK phone number
            </div>
          </div>

          <p>
            <button type="submit" class="btn btn-success">Save</button>
            <a href="<?=autoUrl($url_path . "/" . $id . "/delete")?>" class="btn btn-danger">Delete</a>
          </p>
        </form>

      </div>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs("js/NeedsValidation.js");
$footer->render();