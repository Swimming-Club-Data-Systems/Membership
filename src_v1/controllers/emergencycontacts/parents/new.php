<?php

$use_white_background = true;
$pagetitle = "New Emergency Contact";

include BASE_PATH . 'views/header.php';
if (isset($renewal_trap) && $renewal_trap) {
  include BASE_PATH . 'views/renewalTitleBar.php';
}

$v = null;
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['POST_DATA'])) {
  $v = $_SESSION['TENANT-' . app()->tenant->getId()]['POST_DATA'];
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['POST_DATA']);
} else {
  $v = [
    'name' => '',
    'relation' => '',
    'num' => ''
  ];
}

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <?php if (!isset($renewal_trap) || !$renewal_trap) { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= autoUrl("emergency-contacts") ?>">Emergency Contacts</a></li>
          <li class="breadcrumb-item active" aria-current="page">Add new</li>
        </ol>
      </nav>
    <?php } ?>

    <h1 class="mb-0">
      Add a new Emergency Contact
    </h1>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AddNewError'])) {
        echo $_SESSION['TENANT-' . app()->tenant->getId()]['AddNewError'];
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['AddNewError']);
      } ?>

      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label" for="name">Name</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="<?= htmlspecialchars($v['name']) ?>" required>
          <div class="invalid-feedback">
            You must provide the name of the emergency contact
          </div>
        </div>


        <div class="mb-3">
          <label class="form-label" for="relation">Relation</label>
          <input type="text" class="form-control" id="relation" name="relation" placeholder="Relation" value="<?= htmlspecialchars($v['relation']) ?>" required>
          <div class="invalid-feedback">
            You must provide the relation so we can decide who is best to call
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="num">Contact Number</label>
          <input type="tel" pattern="\+{0,1}[0-9]*" class="form-control" id="num" name="num" placeholder="Phone" value="<?= htmlspecialchars($v['num']) ?>" required>
          <div class="invalid-feedback">
            You must provide a valid UK phone number
          </div>

        </div>
        <button type="submit" class="btn btn-success">Add</button>
      </form>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
