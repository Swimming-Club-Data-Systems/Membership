<?php

$db = app()->db;

$start = 0;

$perPage = 20;

$pagination = new \SCDS\Pagination();

$page = $pagination->get_page();

$sql = $db->query("SELECT COUNT(*) FROM auditLogging INNER JOIN users ON auditLogging.User = users.UserID WHERE `Event` != 'HTTP_REQUEST'");
$numLogs  = $sql->fetchColumn();
$numPages = ((int)($numLogs / $perPage)) + 1;

if ($pagination->get_limit_start() > $numLogs) {
  halt(404);
}

$pagination->records($numLogs);
$pagination->records_per_page($perPage);

$sql = $db->prepare("SELECT `Forename`, `Surname`, `UserID`, `Active`, auditLogging.ID, `Event`, `Description`, `Time`, tenants.ID TID FROM `auditLogging` INNER JOIN `users` ON auditLogging.User = users.UserID INNER JOIN tenants ON users.Tenant = tenants.ID WHERE `Event` != 'HTTP_REQUEST' ORDER
BY `Time` DESC LIMIT :offset, :num");
$sql->bindValue(':offset', $pagination->get_limit_start(), PDO::PARAM_INT);
$sql->bindValue(':num', $perPage, PDO::PARAM_INT);
$sql->execute();

$row = $sql->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Logs - Audit Logs";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/audit")) ?>">Audit</a></li>
      <li class="breadcrumb-item active" aria-current="page">Logs</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col">
      <h1>Logs</h1>

      <?php if ($row) { ?>

        <ul class="list-group">
          <?php do {

            $time = new DateTime($row['Time'], new DateTimeZone('UTC'));
            $time->setTimezone(new DateTimeZone('Europe/London'));
            $tenant = Tenant::fromId($row['TID']);

          ?>
            <li class="list-group-item">
              <div class="row">
                <div class="col">
                  <strong><a target="_blank" href="<?= htmlspecialchars(autoUrl($tenant->getCodeId() . '/users/' . $row['UserID'])) ?>"><?= htmlspecialchars($row['Forename'] . ' ' . $row['Surname']) ?></a></strong>
                </div>
                <div class="col text-center">
                  <strong><?= htmlspecialchars($time->format('H:i:s d/m/Y')) ?></strong>
                </div>
                <div class="col text-end">
                  <strong><a target="_blank" href="<?= htmlspecialchars(autoUrl($tenant->getCodeId())) ?>"><?= htmlspecialchars($tenant->getName()) ?></a></strong>
                </div>
              </div>
              <p class="mb-0 font-monospace">
                <?= htmlspecialchars($row['Event']) ?>
              </p>
              <p class="mb-0">
                <?= htmlspecialchars($row['Description']) ?>
              </p>
            </li>
          <?php } while ($row = $sql->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>

        <?= $pagination->render() ?>

      <?php } else { ?>
        <div class="alert alert-info">
          <p class="mb-0">
            <strong>There are no log items to display</strong>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
