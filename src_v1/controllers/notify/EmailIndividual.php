<?php
$use_white_background = true;

if (is_null($user)) {
  halt(400);
}

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE
UserID = ? AND Tenant = ?");
$query->execute([
  $user,
  $tenant->getId()
]);
$userInfo = $query->fetchAll(PDO::FETCH_ASSOC);
$query->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  $tenant->getId()
]);
$curUserInfo = $query->fetchAll(PDO::FETCH_ASSOC);

if (sizeof($userInfo) != 1) {
  halt(404);
}

$mySwimmer = null;
if (!isset($userOnly) || !$userOnly) {
  $swimmerDetails = $db->prepare("SELECT MForename fn, MSurname sn, MemberID id FROM `members` WHERE `members`.`MemberID` = ? AND members.Tenant = ?");
  $swimmerDetails->execute([
    $id,
    $tenant->getId()
  ]);
  $mySwimmer = $swimmerDetails->fetch(PDO::FETCH_ASSOC);
}

$userInfo = $userInfo[0];
$curUserInfo = $curUserInfo[0];

$name = $userInfo['Forename'] . ' ' . $userInfo['Surname'];
$email = $userInfo['EmailAddress'];
$myName = $curUserInfo['Forename'] . ' ' . $curUserInfo['Surname'];

$replyMe = false;
if (getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'NotifyReplyAddress')) {
  $replyMe = true;
}

$subject = $content = $reply = "";
$from = "current-user";
if (!$replyMe) {
  $reply = "0";
} else {
  $reply = "1";
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivPostContent']['from'])) {
  $from = $_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivPostContent']['from'];
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivPostContent']['ReplyToMe'])) {
  $reply = $_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivPostContent']['ReplyToMe'];
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivPostContent']['subject'])) {
  $subject = $_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivPostContent']['subject'];
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivPostContent']['message'])) {
  $content = $_SESSION['TENANT-' . app()->tenant->getId()]['NotifyIndivPostContent']['message'];
}

$pagetitle = "Email " . htmlspecialchars($name);
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php";

?>

<div class="bg-light py-3 mt-n3 mb-3">
  <div class="container-xl">

    <?php if (isset($userOnly) && $userOnly) { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users")) ?>">Users</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("users/" . $user)) ?>"><?= htmlspecialchars(mb_substr($userInfo['Forename'], 0, 1)) ?><?= htmlspecialchars(mb_substr($userInfo['Surname'], 0, 1)) ?></a></li>
          <li class="breadcrumb-item active" aria-current="page">Email</li>
        </ol>
      </nav>
    <?php } else { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("swimmers")) ?>">Members</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("swimmers/" . $id)) ?>"><?= htmlspecialchars(mb_substr($mySwimmer['fn'], 0, 1)) ?><?= htmlspecialchars(mb_substr($mySwimmer['sn'], 0, 1)) ?></a></li>
          <li class="breadcrumb-item active" aria-current="page">Contact parent</li>
        </ol>
      </nav>
    <?php } ?>

    <h1>Contact a user</h1>
    <p class="lead mb-0">Send an email<?php if ($mySwimmer) { ?> to <?= htmlspecialchars(\SCDS\Formatting\Names::format($mySwimmer['fn'], $mySwimmer['sn'])) ?>'s account<?php } ?></p>

  </div>
</div>

<div class="container-xl">

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']) { ?>
    <div class="alert alert-success">
      <p class="mb-0"><strong>Results have been uploaded</strong>.</p>
    </div>
  <?php
    unset($_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess']);
  } ?>

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['FormError']) { ?>
    <div class="alert alert-danger">
      <p class="mb-0"><strong>We could not verify the integrity of the submitted form</strong>. Please try again.</p>
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

  <form method="post" onkeypress="return event.keyCode != 13;" class="needs-validation" novalidate id="notify-form" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label" for="recipient">To</label>
      <input type="text" class="form-control" name="recipient" id="recipient" placeholder="Recipient" autocomplete="off" value="<?= htmlspecialchars($name . " <" . $email . ">") ?>" disabled>
    </div>

    <div class="row">
      <div class="col-md">
        <div class="mb-3">
          <label class="form-label" for="from">Send message as</label>
          <div class="form-check">
            <input type="radio" id="from-club" name="from" class="form-check-input" value="club-sending-account" <?php if ($from == "club-sending-account") { ?>checked<?php } ?> required>
            <label class="form-check-label" for="from-club"><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></label>
          </div>
          <div class="form-check">
            <input type="radio" id="from-user" name="from" class="form-check-input" value="current-user" <?php if ($from == "current-user") { ?>checked<?php } ?>>
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
            <input type="radio" id="ReplyTo-Club" name="ReplyToMe" class="form-check-input" value="0" <?php if ($reply == "0") { ?>checked<?php } ?> required>
            <label class="form-check-label" for="ReplyTo-Club">Main club address</label>
          </div>
          <div class="form-check">
            <input type="radio" id="ReplyTo-Me" name="ReplyToMe" class="form-check-input" value="1" <?php if (!$replyMe) { ?>disabled<?php } ?> <?php if ($reply == "1") { ?>checked<?php } ?>>
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
      <input type="text" class="form-control" name="subject" id="subject" placeholder="Message Subject" autocomplete="off" value="<?= htmlspecialchars($subject) ?>" required>
      <div class="invalid-feedback">
        You must enter a subject
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="message">Your Message</label>
      <textarea class="form-control" id="message" name="message" rows="10" required><?= htmlspecialchars($content) ?></textarea>
      <small id="messageHelp" class="form-text text-muted">
        Styling will be stripped from this message
      </small>
      <div class="invalid-feedback">
        Include content in your email
      </div>
    </div>

    <input type="hidden" name="MAX_FILE_SIZE" value="3145728">

    <div class="mb-3">
      <label class="form-label text-truncate" for="file-upload">Choose file(s)</label>
      <input type="file" class="form-control" id="file-upload" name="file-upload[]" multiple data-max-total-file-size="10485760" data-max-file-size="3145728" data-error-message-id="file-upload-invalid-feedback">
      <div class="invalid-feedback" id="file-upload-invalid-feedback">
        Oh no!
      </div>
    </div>

    <?php if (isset($swimmer)) { ?>
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" aria-describedby="coach-help" id="coach-send" name="coach-send" value="1" checked>
          <label class="form-check-label" for="coach-send">BCC coaches</label>
          <small id="coach-help" class="form-text text-muted">
            Send a blind carbon-copy of this email to coaches of this member's squads. The member will not be aware coaches were sent a copy of the email.
          </small>
        </div>
      </div>
    <?php } ?>

    <?= SCDS\CSRF::write() ?>

    <p><button class="btn btn-success" id="submit" value="submitted" type="submit">Send the email</button></p>
  </form>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/tinymce/5/tinymce.min.js");
$footer->addJS("js/notify/TinyMCE.js");
$footer->addJS("js/notify/FileUpload.js");
$footer->render();
