<?php

$db = app()->db;
$tenant = app()->tenant;

$admin = app()->user->hasPermissions(['Admin', 'Coach']);

// Get all countries
$countries = getISOAlpha2CountriesWithHomeNations();

$updated = false;

$query = $db->prepare("SELECT * FROM members WHERE MemberID = ? AND Tenant = ?");
$query->execute([
  $id,
  $tenant->getId()
]);
$row = $query->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

$isMemberUser = $row['UserID'] == app()->user->getId();

if (!$admin && $row['UserID'] != app()->user->getId()) {
  halt(404);
}

$getClubCategories = $db->prepare("SELECT `ID`, `Name` FROM `clubMembershipClasses` WHERE `Tenant` = ? AND `Type` = 'club' ORDER BY `Name` ASC");
$getClubCategories->execute([
  $tenant->getId()
]);
$clubCategory = $getClubCategories->fetch(PDO::FETCH_ASSOC);

$getNGBCategories = $db->prepare("SELECT `ID`, `Name` FROM `clubMembershipClasses` WHERE `Tenant` = ? AND `Type` = 'national_governing_body' ORDER BY `Name` ASC");
$getNGBCategories->execute([
  $tenant->getId()
]);
$ngbCategory = $getNGBCategories->fetch(PDO::FETCH_ASSOC);

$member = new Member($id);
$photoPermissions = $member->getPhotoPermissions();
$photo = [];
foreach ($photoPermissions['allowed'] as $permission) {
  $photo[$permission->getType()] = $permission->isPermitted();
}
foreach ($photoPermissions['disallowed'] as $permission) {
  $photo[$permission->getType()] = $permission->isPermitted();
}

$date18 = new DateTime('-18 years', new DateTimeZone('Europe/London'));
$date18 = $date18->format('Y-m-d');

// AuditLog::new('Members-Edited', 'Edited ' . $forename . ' ' . $surname . ' (#' . $id . ')');

$pagetitle = "Edit " . htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) . " - Members";

$date = new DateTime('now', new DateTimeZone('Europe/London'));

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= autoUrl("members") ?>">Members</a></li>

        <li class="breadcrumb-item"><a href="<?= autoUrl("members/" . $id) ?>"><?= htmlspecialchars($row["MForename"]) ?> <?= htmlspecialchars(mb_substr($row["MSurname"], 0, 1, 'utf-8')) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-md-8">
        <h1>Editing <?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?> </h1>
      </div>
      <div class="col text-md-end">
        <button type="submit" class="btn btn-success" form="edit-form">Save</button>
        <a class="btn btn-dark-l btn-outline-light-d" href="<?= htmlspecialchars(autoUrl("members/$id")) ?>">Back</a>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['SuccessState'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>Changes saved successfully</strong>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['SuccessState']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>An error occured trying to save your changes</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']) ?>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
      } ?>

      <form id="edit-form" method="post" class="needs-validation" novalidate>

        <input type="hidden" name="is-admin" value="<?= htmlspecialchars((int) $admin) ?>">
        <input type="hidden" name="is-member-user" value="<?= htmlspecialchars((int) $isMemberUser) ?>">

        <h2>Basic Information</h2>

        <div class="row">
          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="forename">First name</label>
              <input type="text" name="forename" id="forename" class="form-control" required min="1" max="255" value="<?= htmlspecialchars($row['MForename']) ?>">
              <div class="invalid-feedback">
                You must enter a first name
              </div>
            </div>
          </div>

          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="middle-names">Middle names</label>
              <input type="text" name="middle-names" id="foremiddle-namesname" class="form-control" max="255" value="<?= htmlspecialchars($row['MMiddleNames']) ?>">
            </div>
          </div>

          <div class="col">
            <div class="mb-3">
              <label class="form-label" for="surname">Last name</label>
              <input type="text" name="surname" id="surname" class="form-control" required min="1" max="255" value="<?= htmlspecialchars($row['MSurname']) ?>">
              <div class="invalid-feedback">
                You must enter a last name
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="dob">Date of birth</label>
          <input type="date" name="dob" id="dob" class="form-control" required max="<?= htmlspecialchars($date->format("Y-m-d")) ?>" pattern="[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($row['DateOfBirth']) ?>">
          <div class="invalid-feedback">
            You must enter a valid date - If you have not been shown a date picker, enter the date in the format YYYY-MM-DD
          </div>
        </div>

        <div class="mb-3">
          <p class="mb-2">
            Sex (for the purposes of competition) <a tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus" title="" data-bs-content="<?php if ($isMemberUser) { ?>Please select the sex you compete under, even if this is not the same as your gender identity. You can select your gender identity (for use internally at <?= htmlspecialchars(app()->tenant->getName()) ?>) below.<?php } else { ?>Select the sex <?= htmlspecialchars($row['MForename']) ?> competes under, even if this is not the same as their gender identity. They can select their gender identity (for use internally at <?= htmlspecialchars(app()->tenant->getName()) ?>) if they visit this page themselves.<?php } ?>" data-original-title="What does this mean?"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
          </p>
          <div class="form-check">
            <input type="radio" id="sex-m" name="sex" class="form-check-input" value="Male" <?php if ($row['Gender'] == 'Male') { ?>checked<?php } ?>>
            <label class="form-check-label" for="sex-m">Male</label>
          </div>
          <div class="form-check">
            <input type="radio" id="sex-f" name="sex" class="form-check-input" value="Female" <?php if ($row['Gender'] == 'Female') { ?>checked<?php } ?>>
            <label class="form-check-label" for="sex-f">Female</label>
          </div>
        </div>

        <?php if ($isMemberUser) { ?>
          <div class="row mb-1">
            <div class="col-lg">
              <div class="card card-body mb-2">
                <div class="mb-3" id="gender-radio">
                  <p class="mb-2">
                    Gender <a tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus" title="" data-bs-content="Your gender identity is a way to describe how you feel about your gender. You might identify your gender as a boy or a girl or something different. This is different from your sex, which is related to your physical body and biology." data-original-title="What is gender identity?"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                  </p>
                  <?php $other = true; ?>
                  <?php if ($row['GenderIdentity'] == '' || $row['GenderIdentity'] == null) $other = false; ?>
                  <div class="form-check">
                    <input type="radio" id="gender-m" name="gender" class="form-check-input" value="M" <?php if ($row['GenderIdentity'] == 'Male') {
                                                                                                          $other = false; ?>checked<?php } ?> required>
                    <label class="form-check-label" for="gender-m">Male</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="gender-f" name="gender" class="form-check-input" value="F" <?php if ($row['GenderIdentity'] == 'Female') {
                                                                                                          $other = false; ?>checked<?php } ?>>
                    <label class="form-check-label" for="gender-f">Female</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="gender-nb" name="gender" class="form-check-input" value="NB" <?php if ($row['GenderIdentity'] == 'Non binary') {
                                                                                                            $other = false; ?>checked<?php } ?>>
                    <label class="form-check-label" for="gender-nb">Non binary</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="gender-o" name="gender" class="form-check-input" value="O" <?php if ($other) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="gender-o">Other (please describe)</label>
                  </div>
                </div>

                <div class="mb-3 mb-0">
                  <label class="form-label" for="gender-custom">Other...</label>
                  <input type="text" name="gender-custom" id="gender-custom" class="form-control" <?php if (!$other) { ?>disabled<?php } else { ?>required value="<?= htmlspecialchars($row['GenderIdentity']) ?>" <?php } ?> max="256">
                  <div class="invalid-feedback">
                    Please enter a custom gender identity.
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg">
              <div class="card card-body mb-2">
                <div class="mb-3" id="gender-pronoun-radio">
                  <p class="mb-2">
                    Gender pronouns <a tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus" title="" data-bs-content="The words we use to refer to someone like, ‘he’, ‘she’ and ‘they’. We allow you to choose your pronouns so that you're never mis-gendered." data-original-title="What are pronouns?"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                  </p>
                  <?php $other = true; ?>
                  <?php if ($row['GenderPronouns'] == '' || $row['GenderPronouns'] == null) $other = false; ?>
                  <div class="form-check">
                    <input type="radio" id="gender-pronoun-m" name="gender-pronoun" class="form-check-input" value="M" <?php if ($row['GenderPronouns'] == 'He/Him/His') {
                                                                                                                          $other = false; ?>checked<?php } ?> required>
                    <label class="form-check-label" for="gender-pronoun-m">He/Him/His</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="gender-pronoun-f" name="gender-pronoun" class="form-check-input" value="F" <?php if ($row['GenderPronouns'] == 'She/Her/Hers') {
                                                                                                                          $other = false; ?>checked<?php } ?>>
                    <label class="form-check-label" for="gender-pronoun-f">She/Her/Hers</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="gender-pronoun-neutral" name="gender-pronoun" class="form-check-input" value="NB" <?php if ($row['GenderPronouns'] == 'They/Them/Theirs') {
                                                                                                                                $other = false; ?>checked<?php } ?>>
                    <label class="form-check-label" for="gender-pronoun-neutral">They/Them/Theirs</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="gender-pronoun-o" name="gender-pronoun" class="form-check-input" value="O" <?php if ($other) { ?>checked<?php } ?>>
                    <label class="form-check-label" for="gender-pronoun-o">Other (please describe)</label>
                  </div>
                </div>

                <div class="mb-3 mb-0">
                  <label class="form-label" for="gender-pronoun-custom">Other...</label>
                  <input type="text" name="gender-pronoun-custom" id="gender-pronoun-custom" class="form-control" <?php if (!$other) { ?>disabled<?php } else { ?>required value="<?= htmlspecialchars($row['GenderPronouns']) ?>" <?php } ?> max="256">
                  <div class="invalid-feedback">
                    Please enter a custom pronoun. Where appropriate, use a similar format to those above.
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <p class="mb-2">
              Show my gender and pronouns to club staff throughout the membership system <a tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus" title="" data-bs-content="Choosing to show your pronouns to club staff helps us make sure you're not mis-gendered. If you do, places we'll show your information include registers, squad lists and gala information (internally). If you choose no, we'll completely hide your chosen gender and pronouns from all club staff." data-original-title="Why show our staff your pronouns?"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
            </p>
            <div class="form-check">
              <input type="radio" id="show-gender-and-pronounds-yes" name="show-gender-and-pronounds" class="form-check-input" value="1" <?php if (bool($row['GenderDisplay']) && !($row['GenderIdentity'] == '' || $row['GenderIdentity'] == null)) { ?>checked<?php } ?>>
              <label class="form-check-label" for="show-gender-and-pronounds-yes">Yes</label>
            </div>
            <div class="form-check">
              <input type="radio" id="show-gender-and-pronounds-no" name="show-gender-and-pronounds" class="form-check-input" value="0" <?php if (!bool($row['GenderDisplay']) || ($row['GenderIdentity'] == '' || $row['GenderIdentity'] == null)) { ?>checked<?php } ?>>
              <label class="form-check-label" for="show-gender-and-pronounds-no">No</label>
            </div>
          </div>

        <?php } ?>

        <?php if ($admin) { ?>
          <h2>Membership Details</h2>

          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="asa"><?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Registration Number</label>
                <input type="text" name="asa" id="asa" class="form-control" value="<?= htmlspecialchars($row['ASANumber']) ?>">
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label class="form-label" for="ngb-cat"><?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership Category</label>
                <select class="form-select overflow-hidden" id="ngb-cat" name="ngb-cat" placeholder="Select" required>
                  <option value="none">No <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> membership</option>
                  <?php do {
                    $selected = '';
                    if ($row['NGBCategory'] == $ngbCategory['ID']) {
                      $selected = ' selected ';
                    } ?>
                    <option value="<?= htmlspecialchars($ngbCategory['ID']) ?>" <?= $selected ?>><?= htmlspecialchars($ngbCategory['Name']) ?></option>
                  <?php } while ($ngbCategory = $getNGBCategories->fetch(PDO::FETCH_ASSOC)); ?>
                </select>
              </div>


            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="club-cat">Club Membership Category</label>
            <select class="form-select overflow-hidden" id="club-cat" name="club-cat" placeholder="Select" required>
              <?php do {
                $selected = '';
                if ($row['ClubCategory'] == $clubCategory['ID']) {
                  $selected = ' selected ';
                } ?>
                <option value="<?= htmlspecialchars($clubCategory['ID']) ?>" <?= $selected ?>><?= htmlspecialchars($clubCategory['Name']) ?></option>
              <?php } while ($clubCategory = $getClubCategories->fetch(PDO::FETCH_ASSOC)); ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="country">Home Nations Country</label>
            <select class="form-select overflow-hidden" id="country" name="country" placeholder="Select">
              <?php foreach ($countries as $key => $value) {
                $selected = '';
                if ($row['Country'] == $key) {
                  $selected = ' selected ';
                } ?>
                <option value="<?= htmlspecialchars($key) ?>" <?= $selected ?>><?= htmlspecialchars($value) ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="row">
            <div class="col-lg">
              <div class="mb-3">
                <p class="mb-2">
                  Club pays <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> fees?
                </p>
                <div class="form-check">
                  <input type="radio" id="sep-no" name="sep" class="form-check-input" <?php if (!bool($row['ASAPaid'])) { ?>checked<?php } ?> value="0">
                  <label class="form-check-label" for="sep-no">No</label>
                </div>
                <div class="form-check">
                  <input type="radio" id="sep-yes" name="sep" class="form-check-input" <?php if (bool($row['ASAPaid'])) { ?>checked<?php } ?> value="1">
                  <label class="form-check-label" for="sep-yes">Yes</label>
                </div>
              </div>
            </div>

            <div class="col-lg">
              <div class="mb-3">
                <p class="mb-2">
                  Club pays Club Membership fees?
                </p>
                <div class="form-check">
                  <input type="radio" id="cp-no" name="cp" class="form-check-input" <?php if (!bool($row['ClubPaid'])) { ?>checked<?php } ?> value="0">
                  <label class="form-check-label" for="cp-no">No</label>
                </div>
                <div class="form-check">
                  <input type="radio" id="cp-yes" name="cp" class="form-check-input" <?php if (bool($row['ClubPaid'])) { ?>checked<?php } ?> value="1">
                  <label class="form-check-label" for="cp-yes">Yes</label>
                </div>
              </div>
            </div>

            <div class="col-lg">
              <div class="mb-3">
                <p class="mb-2">
                  Swimmer membership status
                </p>
                <div class="form-check">
                  <input type="radio" id="member-status-active" name="member-status" class="form-check-input" checked disabled value="1">
                  <label class="form-check-label" for="member-status-active">Active</label>
                </div>
                <div class="form-check">
                  <input type="radio" id="member-status-suspended" name="member-status" class="form-check-input" disabled value="0">
                  <label class="form-check-label" for="member-status-suspended">Suspended</label>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>

        <h2>Notes</h2>

        <div class="mb-3">
          <label>Medical notes</label>
          <a class="d-block" href="<?= htmlspecialchars(autoUrl("members/$id/medical")) ?>" target="_self">Edit medical notes</a>
        </div>

        <div class="mb-3">
          <label class="form-label" for="other-notes">Other Notes</label>
          <textarea class="form-control" id="other-notes" name="other-notes" rows="3" placeholder="Tell us any other notes for coaches"><?= htmlspecialchars($row['OtherNotes']) ?></textarea>
        </div>

        <?php if ($isMemberUser) { ?>
          <div id="photography-permissions" class="" data-date-eighteen="<?= htmlspecialchars($date18) ?>">
            <h2>Photography permissions</h2>

            <p>
              You should complete this form after <a href="https://www.swimming.org/swimengland/wavepower-child-safeguarding-for-clubs/">reading the Swim England Photography and Filming guidance contained in Wavepower (opens in new tab)</a> and the <a href="<?= htmlspecialchars(autoUrl('privacy')) ?>" target="_blank"><?= htmlspecialchars($tenant->getName()) ?> Privacy Policy</a>.
            </p>

            <p>
              <?= htmlspecialchars($tenant->getName()) ?> may wish to take photographs or film individual or groups of members under the age of 18 that may include <?= htmlspecialchars($member->getFullName()) ?> during their membership of <?= htmlspecialchars($tenant->getName()) ?>. All photographs and filming and all use of such images will be in accordance with the Swim England Photography and Filming Guidance and <?= htmlspecialchars($tenant->getName()) ?>'s Privacy Policy.
            </p>

            <p>
              <?= htmlspecialchars($tenant->getName()) ?> will take all reasonable steps to ensure images and any footage is being used solely for their intended purpose and not kept for any longer than is necessary for that purpose. If you have any concerns or questions about how they are being used please contact the Welfare Officer to discuss this further.
            </p>

            <p>
              As a parent/guardian please complete the below in respect of <?= htmlspecialchars($member->getFullName()) ?>. We encourage all parents/guardians to discuss and explain their choices with their child/ren. Please note that either you or your child can withdraw consent or object to a particular type of use by notifying the Welfare Officer at any time and changing the consents given from within your club account.
            </p>

            <p>
              As the parent/guardian of <?= htmlspecialchars($member->getFullName()) ?> I am happy for:
            </p>

            <div class="card mb-3">
              <div class="card-header">
                Media uses
              </div>
              <div class="list-group list-group-flush">
                <div class="list-group-item">
                  <div class="mb-2">
                    <?= htmlspecialchars($member->getForename()) ?>'s photograph to be used on the <?= htmlspecialchars($tenant->getName()) ?> website.
                  </div>

                  <div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="website-yes" name="website" value="1" <?php if (isset($photo['Website']) && $photo['Website']) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="website-yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="website-no" name="website" value="0" <?php if (!(isset($photo['Website']) && $photo['Website'])) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="website-no">No</label>
                    </div>
                  </div>
                </div>

                <div class="list-group-item">
                  <div class="mb-2">
                    <?= htmlspecialchars($member->getForename()) ?>'s photograph to be used on <?= htmlspecialchars($tenant->getName()) ?> social media platform/s.
                  </div>

                  <div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="social-yes" name="social" value="1" <?php if (isset($photo['Social']) && $photo['Social']) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="social-yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="social-no" name="social" value="0" <?php if (!(isset($photo['Social']) && $photo['Social'])) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="social-no">No</label>
                    </div>
                  </div>
                </div>

                <div class="list-group-item">
                  <div class="mb-2">
                    <?= htmlspecialchars($member->getForename()) ?>'s photograph to be used within other printed publications such as newspaper articles about <?= htmlspecialchars($tenant->getName()) ?>.
                  </div>

                  <div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="noticeboard-yes" name="noticeboard" value="1" <?php if (isset($photo['Noticeboard']) && $photo['Noticeboard']) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="noticeboard-yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="noticeboard-no" name="noticeboard" value="0" <?php if (!(isset($photo['Noticeboard']) && $photo['Noticeboard'])) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="noticeboard-no">No</label>
                    </div>
                  </div>
                </div>

                <div class="list-group-item">
                  <div class="mb-2">
                    <?= htmlspecialchars($member->getForename()) ?>'s photograph to be taken by a professional photographer employed by <?= htmlspecialchars($tenant->getName()) ?> as the official photographer at competitions, galas and other organisational events.
                  </div>

                  <div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="pro-photo-yes" name="pro-photo" value="1" <?php if (isset($photo['ProPhoto']) && $photo['ProPhoto']) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="pro-photo-yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="pro-photo-no" name="pro-photo" value="0" <?php if (!(isset($photo['ProPhoto']) && $photo['ProPhoto'])) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="pro-photo-no">No</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                Training uses (training videos to be deleted once the relevant training is complete)
              </div>
              <div class="list-group list-group-flush">
                <div class="list-group-item">
                  <div class="mb-2">
                    <?= htmlspecialchars($member->getForename()) ?> to be filmed by <?= htmlspecialchars($tenant->getName()) ?> for training purposes.
                  </div>

                  <div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="film-training-yes" name="film-training" value="1" <?php if (isset($photo['FilmTraining']) && $photo['FilmTraining']) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="film-training-yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="film-training-no" name="film-training" value="0" <?php if (!(isset($photo['FilmTraining']) && $photo['FilmTraining'])) { ?>checked<?php } ?>>
                      <label class="form-check-label" for="film-training-no">No</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <p>
              If you do not give your consent, please also inform <?= htmlspecialchars($row['MForename']) ?> so that they know, if possible, not to get into any photos.
            </p>
          </div>
        <?php } ?>

        <p>
          <button type="submit" class="btn btn-primary">
            Save
          </button>
        </p>

      </form>

      <?php if ($admin) { ?>
        <hr>

        <div class="card card-body">
          <h2>Delete member</h2>
          <p>
            By deleting this member, we will remove all personal information. Some information will be retained for the completeness of your club's records.
          </p>
          <p class="mb-0">
            <button data-ajax-url="<?= htmlspecialchars(autoUrl("members/delete")) ?>" data-members-url="<?= htmlspecialchars(autoUrl("members")) ?>" data-member-id="<?= htmlspecialchars($id) ?>" data-member-name="<?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?>" id="delete-button" class="btn btn-danger">
              Delete member
            </button>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>


<!-- Modal for use by JS code -->
<div class="modal fade" id="main-modal" tabindex="-1" role="dialog" aria-labelledby="main-modal-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="main-modal-title">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body" id="main-modal-body">
        ...
      </div>
      <div class="modal-footer" id="main-modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="modal-confirm-button" class="btn btn-success">Confirm</button>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->addJS("js/members/edit.js");
if ($admin) {
  $footer->addJS("js/members/delete.js");
}
$footer->render();
