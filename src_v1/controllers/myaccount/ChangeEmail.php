<?php
  $pagetitle = "Change Your Email Address";
  include BASE_PATH . "views/header.php";
?>
<div class="container-xl">
<div class="mb-3 p-3 bg-white rounded shadow">
<h1>Change your email address</h1>
<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
  echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
} ?>
<form method="post" action="password">
	<p class="mb-0">Your email address is currently <?= $_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'] ?></p>
   <hr>
   <div class="mb-3">
      <label class="form-label" for="newemail">New Email Address</label>
      <input type="newemail" class="form-control" name="newemail" id="newemail" placeholder="hello@example.com">
  </div>
  <p><input type="submit" name="submit" id="submit" class="btn btn-outline-dark" value="Save Changes"></p>
</form>
<p class="mb-0">Changes will take place instantly, if everything is correct. You can change your password as often as you like.</p>
</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>
