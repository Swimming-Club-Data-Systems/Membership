<?php

$tenant = app()->tenant;

$pagetitle = "Request a Trial Form";
$use_white_background = true;
$use_website_menu = true;

$value = [
  'forename' => '',
  'surname' => '',
  'email-addr' => '',
  'swimmer-forename' => '',
  'swimmer-surname' => '',
  'day' => '',
  'month' => '',
  'year' => '',
  'sex' => '',
  'experience' => '',
  'swimmer-xp' => '',
  'swimmer-med' => '',
  'swimmer-club' => '',
  'swimmer-asa' => '',
  'questions' => '',
];
$noValues = true;
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-FC'])) {
  $value = $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-FC'];
  $noValues = false;
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother'])) {
  $value = $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother'];
  $noValues = false;
}

include BASE_PATH . 'views/header.php';

?>

<!--

<div class="container-xl">
  <h1>Request a Trial</h1>
  <div class="row">
    <div class="col-md-10 col-lg-8">
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Success'] === false) { ?>
        <div class="alert alert-danger">
          We were unable to send you a confirmation email. We may not have received your request. Please check your email address or try again later.
        </div>
      <?php } ?>
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors'])) { ?>
        <div class="alert alert-danger">
          There was a problem with some of the information you supplied. Please check;
          <ul class="mb-0">
          <?php foreach ($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors'] as $error) { ?>
            <li><?=$error?></li>
          <?php } ?>
          </ul>
        </div>
      <?php } ?>
      <p class="lead">
        It's great that you want to request a trial at <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>. To help us
        work out the best possible plan for you or your child, please fill in the
        details below.
      </p>
      <p>
        We'll be in touch as soon as we can with details about a trial. At busy
        times, this may take a few days.
      </p>
      <form method="post" <?php if (!$noValues) { ?>class="was-validated"<?php } else { ?>class="needs-validation" novalidate<?php } ?>>
        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') { ?>
        <div id="begin">
          <h2>Before we start</h2>
          <p>
            Are you applying for yourself or on behalf of a minor?
          </p>
          <div class="row mb-3" id="apply-for-option">
            <div class="col-md">
              <button id="apply-for-child" class="btn btn-light btn-block" type="button" value="minor">A Minor</button>
            </div>
            <div class="col-md">
              <button id="apply-for-myself" class="btn btn-light btn-block" type="button" value="myself">Myself</button>
            </div>
          </div>
        </div>
        <?php } ?>
        <h2>About you</h2>
        <p>
          We just need some details about you so we can get in touch.
        </p>
        <div class="mb-3">
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
          <label class="form-label" for="email">Email address</label>
          <input type="email" name="email-addr" id="email-addr" class="form-control" placeholder="abc@example.com" value="<?=htmlspecialchars(trim($value['email-addr']))?>" required>
          <div class="invalid-feedback">
            Please enter a valid email.
          </div>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>

        <div id="swimmers">
          <div class="hide-if-parent">
            <h2>About the swimmer</h2>
            <p>
              Tell us everything you can about yourself or your swimmer. This helps us determine what type of trial to give them and what type of squad they might be best placed in.
            </p>
          </div>

          <div class="mb-3">
            <div class="row mb-3 hide-if-parent">
              <div class="col">
                <label class="form-label" for="swimmer-forename">First name</label>
                <input type="text" name="swimmer-forename" id="swimmer-forename" class="form-control" placeholder="First name" value="<?=htmlspecialchars(trim($value['swimmer-forename']))?>" required>
                <div class="invalid-feedback">
                  Please enter a first name.
                </div>
              </div>
              <div class="col">
                <label class="form-label" for="swimmer-surname">Last name</label>
                <input type="text" name="swimmer-surname" id="swimmer-surname" class="form-control" placeholder="Last name" value="<?=htmlspecialchars(trim($value['swimmer-surname']))?>" required>
                <div class="invalid-feedback">
                  Please enter a last name.
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label>Date of Birth</label>
              <div class="row">
                <div class="col">
                  <select class="form-select" id="day" name="day" required>
                    <?php if ($value['day'] == null | $value['day'] == "") { ?>
                    <option disabled selected>Day</option>
                    <?php } else {?>
                    <option disabled>Day</option>
                    <?php } ?>
                    <?php for ($i = 1; $i < 32; $i++) {
                    $day = $i;
                    if ($day < 10) {
                      $day = "0" . $day;
                    }
                    $selected = "";
                    if ($day == $value['day']) {
                      $selected = "selected";
                    } ?>
                    <option value="<?=$day?> <?=$selected?>"><?=$i?></option>
                    <?php } ?>
                  </select>
                  <div class="invalid-feedback">
                    Please select a day.
                  </div>
                </div>
                <div class="col">
                  <select class="form-select" id="month" name="month" required>
                    <?php if ($value['month'] == null | $value['month'] == "") { ?>
                    <option disabled selected>Month</option>
                    <?php } else {?>
                    <option disabled>Month</option>
                    <?php } ?>
                    <?php for ($i = 0; $i < 12; $i++) {
                    $month = $i+1;
                    if ($month < 10) {
                      $month = "0" . $month;
                    }
                    $selected = "";
                    if ($month == $value['month']) {
                      $selected = "selected";
                    } ?>
                    <option value="<?=$month?>" <?=$selected?>><?=date("M", strtotime(date("Y-") . $month))?></option>
                    <?php } ?>
                  </select>
                  <div class="invalid-feedback">
                    Please select a month.
                  </div>
                </div>
                <div class="col">
                  <select class="form-select" id="year" name="year" required>
                    <?php if ($value['year'] == null | $value['year'] == "") { ?>
                    <option disabled selected>Year</option>
                    <?php } else {?>
                    <option disabled>Year</option>
                    <?php } ?>
                    <?php
                    $year = (int) date("Y");
                    $selected = "";
                    if ($year == $value['year']) {
                      $selected = "selected";
                    }
                    for ($i = $year; $i > $year-100; $i--) { ?>
                    <option value="<?=$i?>" <?=$selected?>><?=$i?></option>
                    <?php } ?>
                  </select>
                  <div class="invalid-feedback">
                    Please select a year.
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="sex">Sex</label>
              <select class="form-select" id="sex" name="sex" required>
                <?php if ($value['sex'] == null | $value['sex'] == "") { ?>
                <option disabled selected>Select a sex</option>
                <?php } else {?>
                <option disabled>Select a sex</option>
                <?php }
                $sex = [];
                if ($value['sex'] == 'Male') {
                  $sex[] = "selected";
                } else if ($value['sex'] == 'Female') {
                  $sex[] = "selected";
                }
                ?>
                <option value="Male" <?=$sex[0]?>>Male</option>
                <option value="Female" <?=$sex[1]?>>Female</option>
              </select>
              <div class="invalid-feedback">
                Please select a sex.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="experience">Level of Experience</label>
              <select class="form-select" id="experience" name="experience" required>
                <?php if ($value['experience'] == null || $value['experience'] == "") { ?>
                <option disabled selected>Select an option</option>
                <?php } else {?>
                <option disabled>Select an option</option>
                <?php }
                $experience = [];
                if ($value['experience'] == 1) {
                  $experience[] = "selected";
                } else if ($value['experience'] == 2) {
                  $experience[] = "selected";
                } else if ($value['experience'] == 3) {
                  $experience[] = "selected";
                } else if ($value['experience'] == 4) {
                  $experience[] = "selected";
                } else if ($value['experience'] == 5) {
                  $experience[] = "selected";
                } else if ($value['experience'] == 6) {
                  $experience[] = "selected";
                }
                ?>
                <option value="1" <?=$experience[0]?>>None</option>
                <option value="2" <?=$experience[1]?>>Duckling</option>
                <option value="3" <?=$experience[2]?>>School Swimming Lessons</option>
                <option value="4" <?=$experience[3]?>>Learn to Swim Stage 1-7</option>
                <option value="5" <?=$experience[4]?>>Learn to Swim Stage 8-10</option>
                <option value="6" <?=$experience[5]?>>Swimming Club</option>
              </select>
              <div class="invalid-feedback">
                Please select an option.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="swimmer-xp">About your Experience</label>
              <textarea name="swimmer-xp" id="swimmer-xp" class="form-control" aria-describedby="xp-help"><?=htmlspecialchars(trim($value['swimmer-xp']))?></textarea>
              <small class="text-muted" id="xp-help">Tell us about any achievements or other details</small>
            </div>

            <div>
              <label class="form-label" for="swimmer-med">Medical Notes (Optional)</label>
              <textarea name="swimmer-med" id="swimmer-med" class="form-control" aria-describedby="med-help"><?=htmlspecialchars(trim($value['swimmer-med']))?></textarea>
              <small class="text-muted" id="med-help">Let us know of any medical notes we should be aware of</small>
            </div>
          </div>

          <h2>Already swim for a club?</h2>
          <p>
            If you/your swimmer is a member of a Swim England swimming club, they
            will have an Swim England Membership Number. If you are not a member of a club,
            skip this box.
          </p>
          <div class="mb-3">
            <label class="form-label" for="swimmer-club">Current Swimming Club (Optional)</label>
            <input type="test" name="swimmer-club" id="swimmer-club" class="form-control" placeholder="<?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>" value="<?=htmlspecialchars(trim($value['swimmer-club']))?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="swimmer-asa">Swim England Number (Optional)</label>
            <input type="number" name="swimmer-asa" id="swimmer-asa" class="form-control" placeholder="123456" pattern="[0-9]*" inputmode="numeric" value="<?=htmlspecialchars(trim($value['swimmer-asa']))?>">
            <div class="invalid-feedback">
              Please enter a number.
            </div>
          </div>
        </div>

        <h2>Final Details</h2>
        <div class="mb-3">
          <label class="form-label" for="questions">Questions or Comments (Optional)</label>
          <textarea name="questions" id="questions" class="form-control"><?=htmlspecialchars(trim($value['questions']))?></textarea>
        </div>

        <p>
          <button class="btn btn-success" type="submit">Request Trial</button>
        </p>

        <p>
          By submitting this form you agree to let <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> contact you by
          email in relation to a trial and becoming a member. Your email address
          will not be used for any other purpose unless you choose to become a
          member. The trial and membership systems will send you a small number
          of automated emails before your trial.
        </p>

        <p>
          <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> will not use your email address for marketing purposes.
        </p>

      </form>
    </div>
  </div>
</div>

-->

<div class="container-xl">
  <h1>Trial requests are currently paused</h1>
  <p>We will be back with a new trial request system soon.</p>
</div>

<?php

unset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-FC']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']);
unset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother']);

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
// $footer->addJS("js/request-a-trial/IsItYouOrYourChild.js");
// $footer->addJS("js/request-a-trial/MultiSwimmers.js");
$footer->render();
