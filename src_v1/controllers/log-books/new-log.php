<?php

$db = app()->db;
$tenant = app()->tenant;

$getMember = $db->prepare("SELECT MForename fn, MSurname sn, members.UserID FROM members WHERE members.MemberID = ? AND members.Tenant = ?");
$getMember->execute([
  $member,
  $tenant->getId()
]);
$memberInfo = $getMember->fetch(PDO::FETCH_ASSOC);

if ($memberInfo == null) {
  halt(404);
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $memberInfo['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
  halt(404);
}

$pagetitle = htmlspecialchars("New log entry - " . \SCDS\Formatting\Names::format($memberInfo['fn'], $memberInfo['sn']));

$title = $entry = "";
$contentType = "text/plain";

$dateObject = new DateTime('now', new DateTimeZone('UTC'));
$dateObject->setTimezone(new DateTimeZone('Europe/London'));
$date = $dateObject->format("Y-m-d");
$time = $dateObject->format("H:i");

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['title'])) {
  $title = $_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['title'];
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['entry'])) {
  $entry = $_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['entry'];
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['content-type'])) {
  $contentType = $_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['content-type'];
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['date'])) {
  $date = $_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['date'];
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['time'])) {
  $time = $_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']['time'];
}
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent'])) {
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['LogEntryOldContent']);
}

include BASE_PATH . 'views/header.php';

?>

<form method="post" class="needs-validation" novalidate>

  <div class="bg-light mt-n3 py-3 mb-3">
    <div class="container-xl">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-MemberLoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-MemberLoggedIn'])) { ?>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("log-books")) ?>">Log book</a></li>
            <li class="breadcrumb-item active" aria-current="page">New</li>
          </ol>
        </nav>
      <?php } else { ?>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("log-books")) ?>">Members</a></li>
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("log-books/members/" . $member)) ?>"><?= htmlspecialchars(mb_substr($memberInfo['fn'], 0, 1, 'utf-8') . mb_substr($memberInfo['sn'], 0, 1, 'utf-8')) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">New</li>
          </ol>
        </nav>
      <?php } ?>

      <div class="row align-items-center">
        <div class="col-lg-8">
          <h1>
            New log book entry
          </h1>
          <p class="lead mb-0">
            <?= htmlspecialchars(\SCDS\Formatting\Names::format($memberInfo['fn'], $memberInfo['sn'])) ?>'s log book
          </p>
          <div class="mb-3 d-lg-none"></div>
        </div>
        <div class="col text-end">
          <p class="mb-0">
            <button type="submit" class="btn btn-success">Save <i class="fa fa-floppy-o" aria-hidden="true"></i></button>
          </p>
        </div>
      </div>

    </div>
  </div>

  <div class="container-xl">
    <div class="row">
      <div class="col-lg-8">

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AddLogErrorMessage'])) { ?>
          <div class="alert alert-danger">
            <?= $_SESSION['TENANT-' . app()->tenant->getId()]['AddLogErrorMessage'] ?>
          </div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['AddLogErrorMessage']);
        } ?>

        <h2>Log entry</h2>
        <div class="mb-3">
          <label class="form-label" for="title">Log title</label>
          <input type="text" required class="form-control" id="title" name="title" placeholder="e.g. Swimming training" value="<?= htmlspecialchars($title) ?>">
          <div class="invalid-feedback">
            You must give this log entry a title.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="entry">Log entry</label>
          <textarea rows="15" required class="form-control" id="entry" name="entry" placeholder="e.g.&#13;&#10;5 x 200m free&#13;&#10;10 x 50m back"><?= htmlspecialchars($entry) ?></textarea>
          <div class="invalid-feedback">
            You must fill out the log entry.
          </div>
        </div>

      </div>
      <div class="col">
        <div class="cell position-sticky top-3">
          <h2>More options</h2>
          <p>
            You can edit the time and date for this activity.
          </p>
          <div class="mb-3">
            <label class="form-label" for="date">Date</label>
            <input class="form-control" id="date" name="date" type="date" value="<?= htmlspecialchars($date) ?>">
            <div class="invalid-feedback">
              You must enter a valid date.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="time">Time</label>
            <input class="form-control" id="time" name="time" type="time" value="<?= htmlspecialchars($time) ?>">
            <div class="invalid-feedback">
              You must enter a valid time.
            </div>
          </div>

          <div class="mb-3 mb-0">
            <label class="form-label" for="content-type">Content type</label>
            <select required class="form-select" id="content-type" name="content-type" aria-describedby="content-type-help">
              <option value="text/plain" <?php if ($contentType == 'text/plain') { ?>selected<?php } ?>>Plain text</option>
              <option value="text/plain-font-monospacespace" <?php if ($contentType == 'text/plain-font-monospacespace') { ?>selected<?php } ?>>font-monospacespaced plain text</option>
              <option value="text/markdown" <?php if ($contentType == 'text/markdown') { ?>selected<?php } ?>>Markdown</option>
            </select>
            <div class="invalid-feedback">
              You must select a content type.
            </div>
            <small id="content-type-help" class="form-text text-muted">
              The content type you select will affect how this log entry gets displayed when you're not editing.
            </small>
          </div>
        </div>
      </div>
    </div>

    <p>
      <button type="submit" class="btn btn-success">Save <i class="fa fa-floppy-o" aria-hidden="true"></i></button>
    </p>

  </div>

</form>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();
