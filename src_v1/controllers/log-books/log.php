<?php

$db = app()->db;
$tenant = app()->tenant;

$getInfo = $db->prepare("SELECT members.MemberID, MForename fn, MSurname sn, members.UserID, trainingLogs.Title, trainingLogs.Content, trainingLogs.ContentType, trainingLogs.DateTime FROM trainingLogs INNER JOIN members ON trainingLogs.Member = members.MemberID WHERE trainingLogs.ID = ? AND members.Tenant = ?");
$getInfo->execute([
  $id,
  $tenant->getId()
]);
$info = $getInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $info['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
  halt(404);
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-MemberLoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-MemberLoggedIn']) && $_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-Member'] != $info['MemberID']) {
  halt(404);
}

$pagetitle = htmlspecialchars("Log #" . htmlspecialchars($id) . " - " . \SCDS\Formatting\Names::format($info['fn'], $info['sn']));

$contentType = "text/plain";
if (isset($info['ContentType'])) {
  $contentType = $info['ContentType'];
}

$dateObject = new DateTime($info['DateTime'], new DateTimeZone('UTC'));
$dateObject->setTimezone(new DateTimeZone('Europe/London'));

$markdown = new ParsedownExtra();

include BASE_PATH . 'views/header.php';

?>

<form method="post" class="needs-validation" novalidate>

  <div class="bg-light mt-n3 py-3 mb-3">
    <div class="container-xl">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-MemberLoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LogBooks-MemberLoggedIn'])) { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("log-books"))?>">Log book</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?=htmlspecialchars($id)?></li>
        </ol>
      </nav>
      <?php } else { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("log-books"))?>">Members</a></li>
          <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("log-books/members/" . $info['MemberID']))?>"><?=htmlspecialchars(mb_substr($info['fn'], 0, 1, 'utf-8') . mb_substr($info['sn'], 0, 1, 'utf-8'))?></a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?=htmlspecialchars($id)?></li>
        </ol>
      </nav>
      <?php } ?>

      <div class="row align-items-center">
        <div class="col-lg-8">
          <h1>
            <?=htmlspecialchars($info['Title'])?>
          </h1>
          <p class="lead mb-0">
            <?=htmlspecialchars($dateObject->format("H:i \\o\\n j F Y"))?>
          </p>
          <div class="mb-3 d-lg-none"></div>
        </div>
        <div class="col text-end">
          <p class="mb-0">
            <a href="<?=htmlspecialchars(autoUrl("log-books/logs/" . $id . "/edit"))?>" class="btn btn-dark-l btn-outline-light-d">Edit <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
          </p>
        </div>
      </div>

    </div>
  </div>

  <div class="container-xl">
    <div class="row">
      <div class="col-lg-8">

        <?php if (mb_strtolower($info['ContentType']) == 'text/markdown') { ?>
        <div class="blog-main">
          <?= $markdown->text($info['Content']) ?>
        </div>
        <?php } else if (mb_strtolower($info['ContentType']) == 'text/plain-font-monospacespace') { ?>
        <div class="font-monospace">
          <?=nl2br(htmlspecialchars($info['Content']))?>
        </div>
        <?php } else { ?>
        <div>
          <?=nl2br(htmlspecialchars($info['Content']))?>
        </div>
        <?php } ?>

      </div>
    </div>
  </div>

</form>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->render();