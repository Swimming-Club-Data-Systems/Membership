<?php
$pagetitle = "Notify Composer";
$use_white_background = true;

$db = app()->db;

$fromEmail = '@' . getenv('EMAIL_DOMAIN');

function fieldChecked($name)
{
  if (isset($_SESSION['SCDS-Notify']['NotifyPostData'][$name]) && bool($_SESSION['SCDS-Notify']['NotifyPostData'][$name])) {
    return ' checked ';
  }
}

function fieldValue($name)
{
  if (isset($_SESSION['SCDS-Notify']['NotifyPostData'][$name])) {
    return 'value="' . htmlspecialchars($_SESSION['SCDS-Notify']['NotifyPostData'][$name]) . '"';
  }
}

$lists = $db->query("SELECT `ID`, `Name` FROM `tenants` ORDER BY `Name` ASC;");

$pagetitle = "Notify Composer - Admin Dashboard - SCDS";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/notify")) ?>">Notify</a></li>
      <li class="breadcrumb-item active" aria-current="page">Composer</li>
    </ol>
  </nav>

  <h1>Notify Composer</h1>
  <p class="lead">Send emails to targeted groups</p>

  <?php if (isset($_SESSION['SCDS-Notify']['UploadSuccess']) && $_SESSION['SCDS-Notify']['UploadSuccess']) { ?>
    <div class="alert alert-success">
      <p class="mb-0"><strong>Results have been uploaded</strong>.</p>
    </div>
  <?php
    unset($_SESSION['SCDS-Notify']['UploadSuccess']);
  } ?>

  <?php if (isset($_SESSION['SCDS-Notify']['FormError']) && $_SESSION['SCDS-Notify']['FormError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>An integrity or idempotency error has occurred</strong></p>
      <p class="mb-0">We were unable to verify that you submitted the form. Please try again.</p>
    </div>
  <?php
    unset($_SESSION['SCDS-Notify']['FormError']);
  } ?>

  <?php if (isset($_SESSION['SCDS-Notify']['UploadError']) && $_SESSION['SCDS-Notify']['UploadError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>There was a problem with the file uploaded</strong>. Please try again.</p>
    </div>
  <?php
    unset($_SESSION['SCDS-Notify']['UploadError']);
  } ?>

  <?php if (isset($_SESSION['SCDS-Notify']['TooLargeError']) && $_SESSION['SCDS-Notify']['TooLargeError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>A file you uploaded was too large</strong>. The maximum size for an individual file is 300000 bytes.</p>
    </div>
  <?php
    unset($_SESSION['SCDS-Notify']['TooLargeError']);
  } ?>

  <?php if (isset($_SESSION['SCDS-Notify']['CollectiveSizeTooLargeError']) && $_SESSION['SCDS-Notify']['CollectiveSizeTooLargeError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>The files you uploaded were collectively too large</strong>. Attachments may not exceed a total of 10 megabytes in size.</p>
    </div>
  <?php
    unset($_SESSION['SCDS-Notify']['CollectiveSizeTooLargeError']);
  } ?>

  <form method="post" id="notify-form" onkeypress="return event.keyCode != 13;" enctype="multipart/form-data" novalidate>

    <div class="mb-3">
      <label>To users in the following tenants...</label>
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
      <textarea class="form-control" id="message" name="message" rows="10" data-tinymce-css-location="<?= htmlspecialchars(autoUrl("public/css/tinymce.css")) ?>" data-documentBaseUrl="<?= htmlspecialchars(autoUrl("notify/new/")) ?>" required><?php if (isset($_SESSION['SCDS-Notify']['NotifyPostData']['message'])) { ?><?= htmlspecialchars($_SESSION['SCDS-Notify']['NotifyPostData']['message']) ?><?php } ?></textarea>
    </div>

    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">

    <div class="mb-3">
      <label class="form-label text-truncate" for="file-upload">Select files to attach</label>
      <input type="file" class="form-control" id="file-upload" name="file-upload[]" multiple data-max-total-file-size="10485760" data-max-file-size="10485760" data-error-message-id="file-upload-invalid-feedback" aria-describedby="file-upload-multi-info">
      <small id="file-upload-multi-info" class="form-text text-muted">
        To upload multiple files, press and hold <kbd>shift</kbd> or <kbd>control</kbd> in the file upload window.
      </small>
      <div class="invalid-feedback" id="file-upload-invalid-feedback">
        Oh no!
      </div>
    </div>

    <div class="mb-3">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" aria-describedby="forceHelp" id="force" name="force" value="1">
        <label class="form-check-label" for="force">Force Send</label>
        <small id="forceHelp" class="form-text text-muted">
          Normally, messages will only be sent to those who have opted in to email
          notifications. Selecting Force Send overrides this. If you do this, you
          must be able to justify your reason for doing so to the System
          Administrator.
        </small>
      </div>
    </div>

    <?= SCDS\CSRF::write() ?>
    <?= SCDS\FormIdempotency::write() ?>

    <p><button class="btn btn-success" id="submit" value="submitted" type="submit">Send the email</button></p>
  </form>
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
        <div class="text-danger">
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

<?php $footer = new \SCDS\RootFooter();
$footer->addJS("js/tinymce/tinymce.min.js");
$footer->addJS("js/notify/TinyMCE.js?v=1");
$footer->addJS("js/notify/FileUpload.js");
$footer->render();
