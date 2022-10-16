<?php

$db = app()->db;

$pagetitle = "Password Reset";
include BASE_PATH . "views/header.php";

?>
<div class="container-xl">
  <div class="">
    <h1>Reset your password</h1>
    <form method="post">
      <div class="row">
        <div class="col-lg-8">
          <div class="mb-3">
            <label class="form-label" for="email-address">
              Email Address
            </label>
            <input type="email" class="form-control text-lowercase" name="email-address"
            id="email-address" placeholder="hello@example.com" required>
            <?=SCDS\CSRF::write()?>
           </div>
           <div class="g-recaptcha mb-3"
           data-sitekey="<?=htmlspecialchars(getenv('GOOGLE_RECAPTCHA_PUBLIC'))?>"
           data-callback="enableBtn"></div>

          <p>
            <input type="submit" name="submit" id="submit" class="btn
            btn-primary btn-lg" value="Request Password Change">
          </p>

          <p>
            If an account exists with email address you submit, we will send you a link by email to reset your password.
          </p>
        </div>
      </div>
    </form>
    <script>
    function enableBtn(){
      document.getElementById("submit").disabled = false;
     }
    document.getElementById("submit").disabled = true;
    </script>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render(); ?>
