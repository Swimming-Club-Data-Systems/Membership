<?php

$db = app()->db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash'], true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT First, Last, Email, Hash FROM joinParents WHERE Hash = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash']]);

$parent = $query->fetch(PDO::FETCH_ASSOC);

$value = $_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData'];
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData'])) {
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData']);
} else {
  $value['forename'] = $parent['First'];
  $value['surname'] = $parent['Last'];
  $value['email-addr'] = $parent['Email'];
}

$pagetitle = "Your details";
$use_white_background = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>Hello <?=$parent['First']?></h1>
  <div class="row">
    <div class="col-sm-10 col-md-8">
      <form method="post" class="needs-validation" novalidate>
        <p class="lead">
          It's great that you want to join <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>. There's a few details
          we'll need to get going.
        </p>

        <h2>About You</h2>
        <p>
          You've filled in some of these details in before when you made your
          trial request. We've filled out the details for you already, but you
          can make changes if you want.
        </p>

        <!--
        <div class="bg-warning p-3 mb-3">
          If you decide to change the email address listed here, you'll need to
          confirm it before you continue
        </div>
        -->

        <div>
          <div class="row mb-3">
            <div class="col">
              <label class="form-label" for="forename">First name</label>
              <input type="text" name="forename" id="forename" class="form-control" placeholder="First name" value="<?=htmlspecialchars(trim($value['forename']))?>" required>
              <div class="invalid-feedback">
                Please enter a first name.
              </div>
            </div>
            <div class="col">
              <label class="form-label" for="surname">Last name</label>
              <input type="text" name="surname" id="surname" class="form-control" placeholder="Last name" value="<?=htmlspecialchars(trim($value['surname']))?>" required>
              <div class="invalid-feedback">
                Please enter a last name.
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="email">Email address</label>
            <input type="email" name="email-addr" id="email-addr" class="form-control" placeholder="abc@example.com" value="<?=htmlspecialchars(trim($value['email-addr']))?>" required>
            <div class="invalid-feedback">
              Please enter a valid email.
            </div>
            <div class="valid-feedback">
              Looks good!
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="mobile">Mobile number</label>
            <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="07123456789" value="<?=htmlspecialchars(trim($value['mobile']))?>" required>
            <div class="invalid-feedback">
              Please enter a number.
            </div>
            <div class="valid-feedback">
              Looks good!
            </div>
          </div>
        </div>

        <h2>Set a password</h2>
        <div>
          <div class="row mb-3">
            <div class="col">
              <label class="form-label" for="password1">Password</label>
              <input type="password" name="password1" id="password1" class="form-control" placeholder="Password" value="<?=htmlspecialchars(trim($value['password1']))?>" required>
              <div class="invalid-feedback">
                Please enter a password.
              </div>
            </div>
            <div class="col">
              <label class="form-label" for="password2">Confirm password</label>
              <input type="password" name="password2" id="password2" class="form-control" placeholder="Password" value="<?=htmlspecialchars(trim($value['password2']))?>" required>
              <div class="invalid-feedback">
                Please enter a password.
              </div>
            </div>
          </div>
        </div>

        <h2>Choose notification preferences </h2>
        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox"
            name="allow-email" id="allow-email" value="1" <?=$email?> checked>
            <label class="form-check-label" for="allow-email">
              I wish to receive important email updates about my squads
            </label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="allow-sms"
            id="allow-sms" value="1" <?=$sms?> checked>
            <label class="form-check-label" for="allow-sms">
              I wish to receive text message notifications
            </label>
          </div>
        </div>

        <p>
          We will still need to send you notifications relating to your
          account from time.
        </p>

        <div class="cell">
          <p class="mb-0"><strong>Legal Stuff Applies</strong></p>
          <p>
            In accordance with European Law, <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>, Chester-le-Street
            ASC Club Digital Services, Swim England and British Swimming are
            Data Controllers for the purposes of the General Data Protection
            Regulation.
          </p>
          <p>
            By proceeding you agree to our <a
            href="https://www.chesterlestreetasc.co.uk/policies/privacy/"
            target="_blank">Privacy Policy</a> and the use of your data by
            <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> and Chester-le-Street ASC Club Digital Services.
            Please note that you have also agreed to our use of you and your
            swimmer's data as part of your registration with the club and with
            British Swimming and Swim England (Formerly known as the ASA).
          </p>
          <p>
            We will be unable to provide this service for technical reasons if
            you do not consent to the use of this data.
          </p>
          <p class="mb-0">
            Contact a member of your committee if you have any questions or email
            <a
            href="mailto:support@chesterlestreetasc.co.uk">support@chesterlestreetasc.co.uk</a>.
          </p>
        </div>

        <p>
          <button class="btn btn-primary" type="submit">
            Next
          </button>
        </p>

      </form>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
