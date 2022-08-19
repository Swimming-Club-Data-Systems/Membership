<?php
$pagetitle = "Notify Composer";
$use_white_background = true;

$db = app()->db;
$tenant = app()->tenant;

$squads = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
  $squads = $db->prepare("SELECT `SquadName`, `SquadID` FROM `squads` WHERE `Tenant` = ? ORDER BY `SquadFee` DESC, `SquadName` ASC;");
  $squads->execute([
    $tenant->getId()
  ]);
} else {
  $squads = $db->prepare("SELECT `SquadName`, `SquadID` FROM `squads` INNER JOIN squadReps ON squadReps.Squad = squads.SquadID WHERE squadReps.User = ? AND `Tenant` = ? ORDER BY `SquadFee` DESC, `SquadName` ASC;");
  $squads->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $tenant->getId()]);
}

$lists = null;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
  $lists = $db->prepare("SELECT targetedLists.ID, targetedLists.Name FROM `targetedLists` WHERE `Tenant` = ? ORDER BY `Name` ASC;");
  $lists->execute([
    $tenant->getId()
  ]);
} else {
  $lists = $db->prepare("SELECT targetedLists.ID, targetedLists.Name FROM `targetedLists` INNER JOIN listSenders ON listSenders.List = targetedLists.ID WHERE listSenders.User = ? AND `Tenant` = ? ORDER BY `Name` ASC;");
  $lists->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $tenant->getId()]);
}

$galas = $db->prepare("SELECT GalaName, GalaID FROM `galas` WHERE GalaDate >= ? AND `Tenant` = ? ORDER BY `GalaName` ASC;");
$date = new DateTime('-1 week', new DateTimeZone('Europe/London'));
$galas->execute([$date->format('Y-m-d'), $tenant->getId()]);

$query = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE
UserID = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$curUserInfo = $query->fetch(PDO::FETCH_ASSOC);

$senderNames = explode(' ', $curUserInfo['Forename'] . ' ' . $curUserInfo['Surname']);
$fromEmail = "";
for ($i = 0; $i < sizeof($senderNames); $i++) {
  $fromEmail .= urlencode(strtolower($senderNames[$i]));
  if ($i < sizeof($senderNames) - 1) {
    $fromEmail .= '.';
  }
}

$pendingRenewal = false;
$date = new DateTime('now', new DateTimeZone('Europe/London'));
$renewals = $db->prepare("SELECT * FROM `renewals` WHERE `StartDate` <= :today AND `EndDate` >= :today AND Tenant = :tenant");
$renewals->execute([
  'tenant' => $tenant->getId(),
  'today' => $date->format("Y-m-d")
]);
$renewal = $renewals->fetch(PDO::FETCH_ASSOC);
if ($renewal) {
  $pendingRenewal = true;
}

if (!app()->tenant->isCLS()) {
  $fromEmail .= '.' . urlencode(mb_strtolower(str_replace(' ', '', getenv('CLUB_CODE'))));
}

$fromEmail .= '@' . getenv('EMAIL_DOMAIN');

function fieldChecked($name)
{
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'][$name]) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'][$name])) {
    return ' checked ';
  }
}

function fieldValue($name)
{
  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'][$name])) {
    return 'value="' . htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData'][$name]) . '"';
  }
}

$uuid = \Ramsey\Uuid\Uuid::uuid4();
$date = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y/m/d');
$attachments = [];

$getCategories = $db->prepare("SELECT `ID` `id`, `Name` `name`, `Description` `description` FROM `notifyCategories` WHERE `Tenant` = ? AND `Active` ORDER BY `Name` ASC;");
$getCategories->execute([
  $tenant->getId()
]);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("notify")) ?>">Notify</a></li>
      <li class="breadcrumb-item active" aria-current="page">Composer</li>
    </ol>
  </nav>

  <h1>Notify Composer</h1>
  <p class="lead">Send emails to targeted groups</p>

  <div class="alert alert-info">
    <p class="mb-0">
      <a href="<?= htmlspecialchars(autoUrl("notify/new/react")) ?>" class="alert-link">Take a look at the new notify composer</a> and give us feedback before we launch it.
    </p>
  </div>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']) { ?>
    <div class="alert alert-success">
      <p class="mb-0"><strong>Results have been uploaded</strong>.</p>
    </div>
  <?php
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>An integrity or idempotency error has occurred</strong></p>
      <p class="mb-0">We were unable to verify that you submitted the form. Please try again.</p>
    </div>
  <?php
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>There was a problem with the file uploaded</strong>. Please try again.</p>
    </div>
  <?php
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadError']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>A file you uploaded was too large</strong>. The maximum size for an individual file is 300000 bytes.</p>
    </div>
  <?php
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['CollectiveSizeTooLargeError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['CollectiveSizeTooLargeError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>The files you uploaded were collectively too large</strong>. Attachments may not exceed a total of 10 megabytes in size.</p>
    </div>
  <?php
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['CollectiveSizeTooLargeError']);
  } ?>

  <form method="post" id="notify-form" onkeypress="return event.keyCode != 13;" enctype="multipart/form-data" novalidate>

    <div class="mb-3">
      <label>To members in the following targeted lists...</label>
      <div class="row">
        <?php while ($list = $lists->fetch(PDO::FETCH_ASSOC)) { ?>
          <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="TL-<?= $list['ID'] ?>" name="TL-<?= $list['ID'] ?>" value="1" <?= fieldChecked('TL-' . $list['ID']) ?>>
              <label class="form-check-label" for="TL-<?= $list['ID'] ?>">
                <?= htmlspecialchars($list['Name']) ?>
              </label>
            </div>
          </div>
        <?php } ?>
        <?php if ($pendingRenewal) { ?>
          <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="pending-renewal" name="pending-renewal" value="1" <?= fieldChecked('pending-renewal') ?>>
              <label class="form-check-label" for="pending-renewal" title="Members who have not yet renewed (autogenerated list)">
                Members pending renewal*
              </label>
            </div>
          </div>

          <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="completed-renewal" name="completed-renewal" value="1" <?= fieldChecked('completed-renewal') ?>>
              <label class="form-check-label" for="completed-renewal" title="Members who have renewed (autogenerated list)">
                Members renewed*
              </label>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>

    <div class="mb-3">
      <label>To members in the following squads...</label>
      <div class="row">
        <?php while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
          <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="<?= $squad['SquadID'] ?>" name="<?= $squad['SquadID'] ?>" value="1" <?= fieldChecked($squad['SquadID']) ?>>
              <label class="form-check-label" for="<?= $squad['SquadID'] ?>">
                <?= htmlspecialchars($squad['SquadName']) ?>
              </label>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>

    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') { ?>
      <div class="mb-3">
        <label>To members entered in the following galas...</label>
        <div class="row">
          <?php while ($gala = $galas->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class="col-6 col-sm-6 col-md-4 col-lg-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="GALA-<?= $gala['GalaID'] ?>" name="GALA-<?= $gala['GalaID'] ?>" value="1" <?= fieldChecked('GALA-' . $gala['GalaID']) ?>>
                <label class="form-check-label" for="GALA-<?= $gala['GalaID'] ?>">
                  <?= htmlspecialchars($gala['GalaName']) ?>
                </label>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

    <div class="row">
      <div class="col-md">
        <div class="mb-3">
          <label class="form-label" for="from">Send message as</label>
          <div class="form-check">
            <input type="radio" id="from-club" name="from" class="form-check-input" value="club-sending-account" <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') { ?>checked<?php } ?> required>
            <label class="form-check-label" for="from-club"><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></label>
          </div>
          <div class="form-check">
            <input type="radio" id="from-user" name="from" class="form-check-input" value="current-user" <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>checked<?php } ?>>
            <label class="form-check-label" for="from-user"><?= htmlspecialchars($curUserInfo['Forename'] . ' ' . $curUserInfo['Surname']) ?></label>
          </div>
          <div class="invalid-feedback">
            Choose a send-as option
          </div>
        </div>
      </div>

      <div class="col-md">
        <div class="mb-3">
          <label class="form-label" for="ReplyToMe">Send replies to</label>
          <div class="form-check">
            <input type="radio" id="ReplyTo-Club" name="ReplyToMe" class="form-check-input" value="0" checked required>
            <label class="form-check-label" for="ReplyTo-Club">Main club address</label>
          </div>
          <div class="form-check">
            <input type="radio" id="ReplyTo-Me" name="ReplyToMe" class="form-check-input" value="1" <?php if (!getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'NotifyReplyAddress')) { ?>disabled<?php } ?>>
            <label class="form-check-label" for="ReplyTo-Me">My reply-to email address</label>
          </div>
          <small class="form-text text-muted">
            <a href="<?= htmlspecialchars(autoUrl("notify/reply-to")) ?>" target="_blank">Manage reply-to address</a>
          </small>
          <div class="invalid-feedback">
            Choose a reply-to address
          </div>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="subject">Message Subject</label>
      <input type="text" class="form-control" name="subject" id="subject" placeholder="Message Subject" autocomplete="off" required <?= fieldValue('subject') ?>>
      <div class="invalid-feedback">
        Please include a message subject
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="message">Your Message</label>
      <p>
        <em>
          Your message will begin with "Hello
          <span class="font-monospace">User Name</span>,".
        </em>
      </p>
      <textarea class="form-control" id="message" name="message" rows="10" data-tinymce-css-location="<?= htmlspecialchars(autoUrl("public/css/tinymce.css")) ?>" data-document-base-url="<?= htmlspecialchars(autoUrl("notify/new/")) ?>" data-images-upload-url="<?= htmlspecialchars(autoUrl("notify/new/image-upload")) ?>" required><?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData']['message'])) { ?><?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyPostData']['message']) ?><?php } ?></textarea>
    </div>

    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">

    <!-- <div class="mb-3">
      <label class="form-label" for="file-upload">Select files to attach</label>
      <input type="file" class="form-control" id="file-upload" name="file-upload[]" multiple data-max-total-file-size="10485760" data-max-file-size="10485760" data-error-message-id="file-upload-invalid-feedback" aria-describedby="file-upload-multi-info">
      <small id="file-upload-multi-info" class="form-text text-muted">
        To upload multiple files, press and hold <kbd>shift</kbd> or <kbd>control</kbd> in the file upload window.
      </small>
      <div class="invalid-feedback" id="file-upload-invalid-feedback">
        Oh no!
      </div>
    </div> -->

    <input type="hidden" name="email-uuid" id="email-uuid" value="<?= htmlspecialchars($uuid) ?>">
    <input type="hidden" name="email-date" id="email-date" value="<?= htmlspecialchars($date) ?>">
    <input type="hidden" name="email-attachments" id="email-attachments" value="<?= htmlspecialchars(json_encode($attachments)) ?>">

    <label class="form-label">Add attachments</label>
    <div class="upload-drop card card-body mb-3" id="upload-zone" data-action="<?= htmlspecialchars(autoUrl('notify/file-uploads')) ?>" data-uuid="<?= htmlspecialchars($uuid) ?>" data-date="<?= htmlspecialchars($date) ?>" data-max-total-file-size="10" data-max-total-file-size-bytes="10485760" data-max-file-size-bytes="10485760">
      <div class="dz-message d-flex flex-column text-center py-2">
        <i class="fa fa-cloud-upload fa-3x" aria-hidden="true"></i>
        Drag &amp; Drop attachments here or click to browse for files
      </div>
      <div class="dropzone-previews row g-2 mb-n2" id="upload-previews"></div>
    </div>
    <p class="d-none text-danger mt-n2" id="file-warning-message"></p>

    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas") { ?>

      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" aria-describedby="forceHelp" id="force" name="force" value="1">
          <label class="form-check-label" for="force">Force Send</label>
          <small id="forceHelp" class="form-text text-muted">
            Normally, messages will only be sent to those who have opted in to email notifications. Selecting Force Send overrides this. If you do this, you must be able to justify your reason for doing so to your System Administrator or Swimming Club Data Systems.
          </small>
        </div>
      </div>

    <?php } ?>

    <div class="mb-3">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" aria-describedby="coach-help" id="coach-send" name="coach-send" value="1" checked>
        <label class="form-check-label" for="coach-send">Send to coaches</label>
        <small id="coach-help" class="form-text text-muted">
          Send a copy of this email to coaches of all selected squads
        </small>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="subscription-category">Notify subscription category</label>
      <select class="form-select" id="subscription-category" name="subscription-category" aria-describedby="subscription-category-help">
        <option value="DEFAULT" selected>Notify</option>
        <?php while ($category = $getCategories->fetch(PDO::FETCH_OBJ)) { ?>
          <option value="<?= htmlspecialchars($category->id) ?>"><?= htmlspecialchars($category->name) ?></option>
        <?php } ?>
      </select>
      <small id="subscription-category-help" class="form-text text-muted">
        Emails will only be sent to those in selected groups who have opted in to receive messages in the above category.<?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Galas") { ?> <strong>Force Send</strong> will override this.<?php } ?> Additonial Recipients excluded.
      </small>
    </div>

    <?= SCDS\CSRF::write() ?>
    <?= SCDS\FormIdempotency::write() ?>

    <p>
      <button class="btn btn-success" id="submit" value="submitted" type="submit">Send the email</button>
      <button class="btn btn-dark-l btn-outline-light-d" id="tinymce-preview" type="button" id="tinymce-preview">Preview message</button>
    </p>
  </form>

  <div class="text-muted small">
    <p>
      * Indicates list is generated automatically by the system.
    </p>
  </div>
</div>

<div class="modal" id="force-alert-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="force-alert-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="force-alert-modal">Are you sure?</h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close">

        </button>
      </div>
      <div class="modal-body">
        <div class="">
          <p>
            <strong>Force sending an email overrides the subscription options of your members.</strong>
          </p>

          <p>
            Under the General Data Protection Regulation, you may only override these preferences in specific cases.
          </p>

          <p class="mb-0">
            SCDS may periodically review your organisation's use of the <em>Force Send</em> functionality.
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Don't force send</button>
        <button type="button" class="btn btn-danger" id="accept">I understand</button>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/tinymce/5/tinymce.min.js");
// $footer->addJS("js/notify/TinyMCE.js?v=1");
// $footer->addJS("js/notify/FileUpload.js");
$footer->addJS("js/dropzone/dropzone.js");
$footer->addJS("js/notify/FileUploadDropzone.js");
$footer->render();
