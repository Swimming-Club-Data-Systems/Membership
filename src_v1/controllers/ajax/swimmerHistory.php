<?php

$db = app()->db;
$tenant = app()->tenant;

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
$count = 0;
if ($access == "Committee" || $access == "Admin" || $access == "Coach" || $access == "Galas") {
  $sql = "";
  $get = null;
  if ((isset($_POST["squadID"])) && (isset($_POST["search"]))) {
    // get the squadID parameter from post
    $squadID = $_POST["squadID"];
    // get the search term parameter from post
    $search = $_POST["search"];

    $getSquads = $db->prepare("SELECT SquadName FROM squads INNER JOIN squadMembers ON squadMembers.Squad = squads.SquadID WHERE squadMembers.Member = ?");

    // Search the database for the results
    if ($squadID == "allSquads") {
      $get = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname, members.ASANumber, members.DateOfBirth FROM members WHERE members.Tenant = ? AND members.MSurname COLLATE utf8mb4_general_ci LIKE ? ORDER BY members.MForename, members.MSurname ASC");
      $get->execute([
        $tenant->getId(),
        '%' . $search . '%'
      ]);
    } else {
      $get = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname, members.ASANumber, members.DateOfBirth FROM members INNER JOIN squadMembers ON members.MemberID = squadMembers.Member WHERE members.Tenant = ? AND squadMembers.Squad = ? AND members.MSurname COLLATE utf8mb4_general_ci LIKE ? ORDER BY members.MForename , members.MSurname ASC");
      $get->execute([
        $tenant->getId(),
        $squadID,
        '%' . $search . '%'
      ]);
    }
  }

  $row = $get->fetch(PDO::FETCH_ASSOC);
  if ($row != null) { ?>
    <ul class="list-group">
      <?php do {
        $getSquads->execute([
          $row['MemberID']
        ]);
        $squad = $getSquads->fetch(PDO::FETCH_ASSOC);
      ?>
        <li class="list-group-item">
          <div class="row">
            <div class="col-md-4">
              <p class="mb-0">
                <strong>
                  <a href="<?= htmlspecialchars(autoUrl("members/" . $row['MemberID'])) ?>"><?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?></a>
                </strong>
              </p>
              <p class="mb-0"><?php if ($squad) {
                                $i = 0;
                                do { ?><?php if ($i > 0) { ?>, <?php } ?><?= htmlspecialchars($squad['SquadName']) ?><?php $i++;
                                                                                                                    } while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)); ?>
              <?php } else { ?>
                No squads
              <?php } ?>
              </p>
              <div class="mb-3 d-md-none"></div>
            </div>
            <div class="col">
              <dl class="row mb-0">
                <dt class="col-md-3">4 Week Rolling Attendance</dt>
                <dd class="col-md-9"><?= getAttendanceByID(null, $row['MemberID'], 4) ?>%</dd>

                <dt class="col-md-3">20 Week History</dt>
                <dd class="col-md-9"><a href="<?= htmlspecialchars(autoUrl("attendance/history/members/" . $row['MemberID'])) ?>">View</a></dd>

                <dt class="col-md-3">Custom History</dt>
                <dd class="col-md-9 mb-0"><a href="<?= htmlspecialchars(autoUrl("attendance/history/members/" . $row['MemberID'] . '/search')) ?>">View</a></dd>
              </dl>
            </div>
          </div>
        </li>
      <?php } while ($row = $get->fetch(PDO::FETCH_ASSOC)); ?>
    </ul>

  <?php } else { ?>
    <div class="alert alert-warning">
      <strong>No members found for that squad</strong> <br>
      Please try another search
    </div>
  <?php } ?>

<?php } else {
  halt(404);
}
