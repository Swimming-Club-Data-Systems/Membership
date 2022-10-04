<?php

$db = app()->db;
$tenant = app()->tenant;

$use_white_background = true;

$start = 0;

$pagination = new \SCDS\Pagination();

$page = $pagination->get_page();

$sql = $db->prepare("SELECT COUNT(*) FROM notifyHistory WHERE Tenant = ?");
$sql->execute([
  $tenant->getId()
]);
$numMails  = $sql->fetchColumn();
$numPages = ((int)($numMails / 10)) + 1;

if ($pagination->get_limit_start() > $numMails) {
  halt(404);
}

$sql = $db->prepare("SELECT `notifyHistory`.`Subject`, `notifyHistory`.`Message`,
`notifyHistory`.`ForceSend`, `Forename`, `Surname`, `JSONData`, `Date` FROM
(`notifyHistory` LEFT JOIN `users` ON notifyHistory.Sender = users.UserID) WHERE notifyHistory.Tenant = :tenant ORDER
BY `Date` DESC LIMIT :offset, :num");
$sql->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
$sql->bindValue(':offset', $pagination->get_limit_start(), PDO::PARAM_INT);
$sql->bindValue(':num', 10, PDO::PARAM_INT);
$sql->execute();

$row = $sql->fetch(PDO::FETCH_ASSOC);

$pagination->records($numMails);
$pagination->records_per_page(10);

$pagetitle = "Page " . $page . " - Message History - Notify";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php"; ?>

<style>
  .force-wrap {

    /* These are technically the same, but use both */
    overflow-wrap: break-word;
    word-wrap: break-word;

    -ms-word-break: break-all;
    /* This is the dangerous one in WebKit, as it breaks things wherever */
    word-break: break-all;
    /* Instead use this non-standard one: */
    word-break: break-word;

    /* Adds a hyphen where the word breaks, if supported (No Blink) */
    -ms-hyphens: auto;
    -moz-hyphens: auto;
    -webkit-hyphens: auto;
    hyphens: auto;

  }

  .force-wrap:last-child,
  .force-wrap p:last-child {
    margin-bottom: 0px;
  }
</style>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("notify")) ?>">Notify</a></li>
      <li class="breadcrumb-item active" aria-current="page">History</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8 col-md-10">
      <h1>Notify message history</h1>
      <?php if ($row == null) { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>There are no previous messages to display</strong>
          </p>
          <p class="mb-0">
            Send an email and it will show up here
          </p>
        </div>
      <?php } else { ?>
        <p class="lead">
          Page <?= htmlspecialchars($page) ?> of <?= htmlspecialchars($numPages) ?>
        </p>
        <?php do {
          $info = json_decode($row['JSONData']);
          $sender = null;
          if ($row['Forename'] != "" && $row['Surname'] != "") {
            $sender = "<dt class=\"col-sm-3\">Sent by</dt><dd class=\"col-sm-9\">" . htmlspecialchars(\SCDS\Formatting\Names::format($row['Forename'], $row['Surname'])) . "</dd>";
            if ($row['ForceSend']) {
              $sender .= "<dt class=\"col-sm-3\">Force Sent</dt><dd class=\"col-sm-9\">True</dd>";
            }
          } else {
            $sender = "<dt class=\"col-sm-3\">Sent by</dt><dd class=\"col-sm-9\">" . htmlspecialchars($info->Sender->Name) . "</dd>";
            if ($row['ForceSend']) {
              $sender .= "<dt class=\"col-sm-3\">Force Sent</dt><dd class=\"col-sm-9\">True</dd>";
            }
          }
        ?>
          <div class="card mb-3">
            <div class="card-header">
              <h5 class="card-title">
                <?= htmlspecialchars($row['Subject']) ?>
              </h5>
              <dl class="row mb-0 small">
                <?php echo $sender; ?>
                <?php if ($row['JSONData'] != "") { ?>
                  <dt class="col-sm-3">Sent to</dt>
                  <dd class="col-sm-9">
                    <?php
                    $squads = [];
                    if (isset($info->To->Squads)) {
                      $squads = (array) $info->To->Squads;
                    }
                    $lists = [];
                    if (isset($info->To->Targeted_Lists)) {
                      $lists = (array) $info->To->Targeted_Lists;
                    }
                    $galas = [];
                    if (isset($info->To->Galas)) {
                      $galas = (array) $info->To->Galas;
                    }
                    $array = array_merge($squads, $lists, $galas);
                    sort($array);
                    foreach ($array as $s) { ?>
                      <span class="badge rounded-pill rounded bg-dark">
                        <?= htmlspecialchars(mb_strimwidth($s, 0, 40)) ?>
                      </span><?php
                            } ?>
                  </dd>
                  <?php if (isset($info->NamedSender->Name)) { ?>
                    <dt class="col-sm-3">Sent as</dt>
                    <dd class="col-sm-9 text-truncate">
                      <?= htmlspecialchars($info->NamedSender->Name) ?>
                    </dd>
                  <?php } ?>
                  <?php if (isset($info->ReplyToMe->Name) && isset($info->ReplyToMe->Email)) { ?>
                    <dt class="col-sm-3">Custom reply to</dt>
                    <dd class="col-sm-9 text-truncate">
                      <a href="mailto:<?= htmlspecialchars($info->ReplyToMe->Email) ?>">
                        <?= htmlspecialchars($info->ReplyToMe->Name) ?> &lt;<?= htmlspecialchars($info->ReplyToMe->Email) ?>&gt;
                      </a>
                    </dd>
                  <?php } ?>
                  <dt class="col-sm-3">Date</dt>
                  <dd class="col-sm-9">
                    <?php
                    $date = new DateTime($row['Date'], new DateTimeZone('UTC'));
                    $date->setTimezone(new DateTimeZone('Europe/London')); ?>
                    <?= $date->format('H:i \o\\n l j F Y') ?>
                  </dd>
                  <?php if (isset($info->Attachments)) { ?>
                    <dt class="col-sm-3 mb-0">Attachments</dt>
                    <dd class="col-sm-9 mb-0">
                      <?php $attachments = (array) $info->Attachments;
                      foreach ($attachments as $a) {
                        $faClass = ' fa-file-o ';
                        $download = false;
                        $disposition = 'inline';

                        if (isset($a->MIME) && $a->MIME) {

                          if ($a->MIME == 'application/pdf') {
                            $faClass = ' fa-file-pdf-o ';
                          }

                          if (mb_substr($a->MIME, 0, mb_strlen('image')) === 'image') {
                            $faClass = ' fa-file-image-o ';
                          }

                          if (mb_substr($a->MIME, 0, mb_strlen('video')) === 'video') {
                            $faClass = ' fa-file-video-o ';
                          }

                          if (mb_substr($a->MIME, 0, mb_strlen('text')) === 'text') {
                            $faClass = ' fa-file-text-o ';
                            $download = true;
                          }

                          if ($a->MIME == 'application/msword' || $a->MIME == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                            $faClass = ' fa-file-word-o ';
                            $download = true;
                          }

                          if ($a->MIME == 'application/vnd.ms-powerpoint' || $a->MIME == 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
                            $faClass = ' fa-file-powerpoint-o ';
                            $download = true;
                          }

                          if ($a->MIME == 'application/vnd.ms-excel' || $a->MIME == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                            $faClass = ' fa-file-excel-o ';
                            $download = true;
                          }
                        } else {
                          $download = true;
                        }

                        if ($download) $disposition = 'attachment';

                      ?>
                        <a href="<?= htmlspecialchars(autoUrl("files/" . $a->URI . "?filename=" . urlencode($a->Filename) . "&disposition=" . urlencode($disposition))) ?>" class="d-block mb-1 text-truncate text-decoration-none" <?php if ($download) { ?> download="" <?php } else { ?> target="_blank" <?php } ?>>
                          <span class="fa <?= htmlspecialchars($faClass) ?> fa-fw"></span> <?= htmlspecialchars($a->Filename) ?>
                        </a>
                      <?php
                      } ?>
                    </dd>
                  <?php } ?>
              </dl>
            <?php } ?>
            </div>
            <div class="card-body force-wrap">
              <?php echo $row['Message']; ?>
            </div>
          </div>
        <?php } while ($row = $sql->fetch(PDO::FETCH_ASSOC)); ?>

        <?= $pagination->render() ?>

      <?php } ?>

    </div>
  </div>
</div>

<?php
$footer = new \SCDS\Footer();
$footer->render();
