<?php

$db = app()->db;


$privacy = app()->tenant->getKey('PrivacyPolicy');

$Extra = new ParsedownExtra();
$Extra->setSafeMode(true);
$search  = array("\n##### ", "\n#### ", "\n### ", "\n## ", "\n# ");
$replace = array("\n###### ", "\n###### ", "\n##### ", "\n#### ", "\n### ");

$privacyPolicy = null;
if ($privacy != null && $privacy != "") {
  $privacyPolicy = $db->prepare("SELECT Content FROM posts WHERE ID = ?");
  $privacyPolicy->execute([$privacy]);
  $privacyPolicy = str_replace($search, $replace, $privacyPolicy->fetchColumn());
  if ($privacyPolicy[0] == '#') {
    $privacyPolicy = '##' . $privacyPolicy;
  }
}

$mode = "Default";
$fam_keys = null;
/*$fam_keys = [
  "FAM" => null,
  "KEY" => null
];*/

$_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationMode'] = $mode;

  $use_white_background = true;
  $pagetitle = "Register";
  $preventLoginRedirect = true;
  include BASE_PATH . "views/header.php";

?>
<div class="pb-3">
  <div class="container-xl">
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationGoVerify'])) {
        echo $_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationGoVerify'];
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationGoVerify']);
      }  else { ?>
      <!--<div class="alert alert-warning">
        <p class="mb-0">
          <strong>
            Registration is currently closed for maintenance
          </strong>
        </p>
        <p>
          We're working to improve this service and will reopen user
          registration as soon as possible.
        </p>

        <p class="mb-0">
          If you've just received a letter about getting registered today, don't
          worry. If you don't need to make gala entries there's no rush yet -
          We'll be back by tomorrow. You will need to ensure you register for
          this system and connect your swimmers to your account by 1 September
          2018 ahead of the new season.
        </p>
      </div>-->
      <h1>User Registration</h1>
      <p>We need a few details before we start.</p>
      <hr>
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
        echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
        unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
      } ?>

      <div class="row">
        <div class="col-md-10 col-lg-8">
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>Are you a parent or club member?</strong>
            </p>
            <p>
              We'll be sending you an email in the next few days. This will include a personalised link to set up your account.
            </p>

            <p>
              Setting up your account using the link sent to you by email allows us to automatically link you/your members to your account and take you through the onboarding process.
            </p>
            
            <p class="mb-0">
              Until you get this, please bear with us otherwise your registration may be delayed.
            </p>
          </div>
        </div>
      </div>
      
      <form method="post" action="<?php echo autoUrl("register"); ?>" name="register" id="register" class="needs-validation" novalidate>

        <h2>About you</h2>
        <div class="row">
          <div class="col-md-10 col-lg-8">

            <div class="row">

              <div class="col">

                <div class="mb-3">
                  <label class="form-label" for="forename">First Name</label>
                  <input class="form-control" type="text" name="forename"
                  id="forename" placeholder="First" required
                  value="<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationForename'])) { ?><?=htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationForename'])?><?php } ?>" autocomplete="given-name">
                  <div class="invalid-feedback">
                    You must provide a first name
                  </div>
                </div>

              </div>

              <div class="col">

                <div class="mb-3">
                  <label class="form-label" for="surname">Last Name</label>
                  <input class="form-control" type="text" name="surname"
                  id="surname" placeholder="Last" required
                  value="<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationSurname'])) { ?><?=htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationSurname'])?><?php } ?>" autocomplete="family-name">
                  <div class="invalid-feedback">
                    You must provide a surname
                  </div>
                </div>

              </div>

            </div>

            <div class="mb-3">
              <label class="form-label" for="email">Email Address</label>
              <input class="form-control mb-0 text-lowercase" type="email" name="email" id="email-address" placeholder="yourname@example.com" required value="<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationEmail'])) { ?><?=htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationEmail'])?><?php } ?>" autocomplete="email">
              <small id="emailHelp" class="form-text text-muted">
                Your email address will only be used inside <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> and SCDS.
              </small>
              <div class="invalid-feedback">
                You must provide a valid email address
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-10 col-lg-8">
            <div class="mb-3">
              <label class="form-label" for="mobile">Mobile Number</label>
              <input class="form-control" type="tel" pattern="\+{0,1}[0-9]*" name="mobile" id="mobile" placeholder="01234 567890" required value="<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationMobile'])) { ?><?=htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationMobile'])?><?php } ?>" autocomplete="tel">
              <small id="mobileHelp" class="form-text text-muted">If you don't have a mobile, use your landline number.</small>
              <div class="invalid-feedback">
                You must provide a valid UK phone number
              </div>
            </div>
          </div>
        </div>

        <!--<h2>Password</h2>-->
        <div class="row">
          <div class="col-md-10 col-lg-8">
            <div class="row">
              <div class="col">
                <div class="mb-3">
                  <label class="form-label" for="password1">Password</label>
                  <input class="form-control" type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" aria-describedby="pwHelp" name="password1" id="password1" placeholder="Password" required autocomplete="new-password">
                  <small id="pwHelp" class="form-text text-muted">Use 8 characters or more, with at least one lowercase letter, at least one uppercase letter and at least one number</small>
                  <div class="invalid-feedback">
                    You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
                  </div>
                </div>
              </div>

              <div class="col">
                <div class="mb-3">
                  <label class="form-label" for="password2">Confirm Password</label>
                  <input class="form-control" type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" name="password2" id="password2" aria-describedby="pwConfirmHelp" placeholder="Password" required autocomplete="new-password">
                  <small id="pwConfirmHelp" class="form-text text-muted">Repeat your password</small>
                  <div class="invalid-feedback">
                    You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <h2>Notification Preferences</h2>

        <?php

        $email = $sms = "";
        if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationEmailAuth'])) {
          $email = " checked ";
          unset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationEmailAuth']);
        }
        if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationSmsAuth'])) {
          $sms = " checked ";
          unset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationSmsAuth']);
        } ?>

        <div class="row">
          <div class="col-md-10 col-lg-8">
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                name="emailAuthorise" id="emailAuthorise" value="1" <?=$email?>>
                <label class="form-check-label" for="emailAuthorise">
                  I wish to receive important email updates about my squads.
                  This includes emails about session cancellations.
                </label>
              </div>
            </div>

            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                name="smsAuthorise" id="smsAuthorise" value="1" <?=$sms?>>
                <label class="form-check-label" for="smsAuthorise">
                  I wish to receive text message notifications
                </label>
              </div>
            </div>

            <p class="small">
              We will still need to send you notifications relating to your
              account from time to time.
            </p>

            <div class="cell">
              <p class="mb-0"><strong>Legal Stuff Applies</strong></p>
              <?php if ($privacyPolicy != null) { ?>
              <?=$Extra->text($privacyPolicy)?>
              <?php } else { ?>
              <p>
                YOUR CLUB HAS NOT SET UP A PRIVACY POLICY. PLEASE DO NOT PROCEED.
              </p>
              <p>
                In accordance with European Law, <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>, Swim England and British Swimming are Data Controllers for the purposes of the General Data Protection Regulation.
              </p>
              <p>
                By proceeding you agree to our <a href="https://www.chesterlestreetasc.co.uk/policies/privacy/" target="_blank">Privacy Policy (this is an example policy)</a> and the use of your data by <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>. Please note that you have also agreed to our use of you and/or your swimmer's data as part of your registration with the club and with British Swimming and Swim England.
              </p>
              <p>
                We will be unable to provide this service for technical reasons if you do not consent to the use of this data.
              </p>
              <p class="mb-0">
                Contact a member of your committee if you have any questions or email <a href="mailto:support@chesterlestreetasc.co.uk">support@chesterlestreetasc.co.uk</a>.
              </p>
              <?php } ?>
            </div>

          </div>

        </div>

        <div class="g-recaptcha mb-3" data-sitekey="<?=htmlspecialchars(getenv('GOOGLE_RECAPTCHA_PUBLIC'))?>" data-callback="enableBtn"></div>

        <?=SCDS\CSRF::write()?>
        <input type="submit" id="submit" class="btn btn-primary btn-lg" value="Register">
      </form>
      <?php } ?>
    </div>
  </div>
</div>

<script>
function enableBtn(){
  document.getElementById("submit").disabled = false;
  }
document.getElementById("submit").disabled = true;
</script>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();

unset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationUsername']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationForename']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationSurname']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationEmail']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationMobile']);
