<?php

$db = app()->db;
$tenant = app()->tenant;

if ($_POST['response'] == "getSwimmers") {
  $swimmers = $db->prepare("SELECT combined.MForename, combined.MSurname, combined.SquadName, combined.ID FROM (SELECT MForename, MSurname, 'Member' AS SquadName, targetedListMembers.ID FROM (`targetedListMembers` INNER JOIN `members` ON members.MemberID = targetedListMembers.ReferenceID) WHERE `targetedListMembers`.`ListID` = :list AND targetedListMembers.ReferenceType = 'Member' AND members.Tenant = :tenant UNION SELECT Forename AS MForename, Surname AS MSurname, 'User' AS SquadName, targetedListMembers.ID FROM (`targetedListMembers` INNER JOIN `users` ON users.UserID = targetedListMembers.ReferenceID) WHERE `targetedListMembers`.`ListID` = :list AND targetedListMembers.ReferenceType = 'User' AND users.Tenant = :tenant) AS combined ORDER BY combined.ID ASC ");

  $swimmers->execute([
    'list' => $id,
    'tenant' => $tenant->getId()
  ]);
  $row = $swimmers->fetch(PDO::FETCH_ASSOC);

?>

  <div class="">
    <?php if ($row != null) { ?>
      <div class="card">
        <div class="card-header">
          List members
        </div>
        <ul class="list-group list-group-flush">
          <?php do { ?>
            <li class="list-group-item">
              <div class="row align-items-center">
                <div class="col-auto">
                  <p class="mb-0">
                    <strong>
                      <?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?>
                    </strong>
                  </p>
                  <p class="mb-0">
                    <?= htmlspecialchars($row['SquadName']) ?>
                  </p>
                </div>
                <div class="col text-end">
                  <button type="button" id="RelationDrop-<?= $row['ID'] ?>" class="btn btn-light" value="<?= $row['ID'] ?>">
                    Remove
                  </button>
                </div>
              </div>
            </li>
          <?php } while ($row = $swimmers->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
      </div>
    <?php } else { ?>
      <div class="alert alert-info mb-0">
        <strong>There are no swimmers linked to this targeted list</strong>
      </div>
    <?php } ?>
  </div>
<?php
} else if ($_POST['response'] == "squadSelect") {
  $status = false;
  $output = ' <option value="null" selected>Select a member</option>';

  try {
    $squad = $_POST['squadSelect'];
    $members = null;
    if ($squad != "all") {
      $members = $db->prepare("SELECT MemberID, MForename, MSurname FROM `members` INNER JOIN squadMembers ON squadMembers.Member = members.MemberID WHERE squadMembers.Squad = ? AND members.Tenant = ? AND MemberID NOT IN (SELECT ReferenceID FROM targetedListMembers WHERE ListID = ? AND ReferenceType = 'Member') ORDER BY `MForename` ASC, `MSurname` ASC");
      $members->execute([
        $squad,
        $tenant->getId(),
        $id
      ]);
    } else {
      $members = $db->prepare("SELECT MemberID, MForename, MSurname FROM `members` WHERE Tenant = ? AND MemberID NOT IN (SELECT ReferenceID FROM targetedListMembers WHERE ListID = ? AND ReferenceType = 'Member') ORDER BY `MForename` ASC, `MSurname` ASC");
      $members->execute([
        $tenant->getId(),
        $id
      ]);
    }
    while ($row = $members->fetch(PDO::FETCH_ASSOC)) {
      $output .= '<option value="' . htmlspecialchars($row['MemberID']) . '">' . htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) . '</option>';
      $status = true;
    }
  } catch (Exception $e) {
    // Do nothing, an empty, disabled select will be returned.
  }

  echo json_encode([
    'swimmerSelectContent' => $output,
    'status' => $status
  ]);
} else if ($_POST['response'] == "userSelect") {
  $status = false;
  $output = ' <option value="null" selected>Search for a user</option>';

  try {
    if (mb_strlen($_POST['searchTerm']) > 0) {
      $searchTerm = '%' . $_POST['searchTerm'] . '%';
      $members = null;
      $members = $db->prepare("SELECT UserID, Forename, Surname FROM `users` WHERE Active AND Tenant = :tenant AND (`Forename` COLLATE utf8mb4_general_ci LIKE :searchTerm OR `Surname` COLLATE utf8mb4_general_ci LIKE :searchTerm) AND UserID NOT IN (SELECT ReferenceID FROM targetedListMembers WHERE ListID = :list AND ReferenceType = 'User') ORDER BY `Forename` ASC, `Surname` ASC");
      $members->execute([
        'tenant' => $tenant->getId(),
        'searchTerm' => $searchTerm,
        'list' => $id
      ]);

      $usersOutput = '<option value="null" selected>Select a user</option>';
      while ($row = $members->fetch(PDO::FETCH_ASSOC)) {
        $usersOutput .= '<option value="' . htmlspecialchars($row['UserID']) . '">' . htmlspecialchars(\SCDS\Formatting\Names::format($row['Forename'], $row['Surname'])) . '</option>';
        $status = true;
      }
      if ($status) {
        $output = $usersOutput;
      }
    }
  } catch (Exception $e) {
    // Do nothing, an empty, disabled select will be returned.
  }

  echo json_encode([
    'userSelectContent' => $output,
    'status' => $status
  ]);
} else if ($_POST['response'] == "insert") {
  $swimmer = $_POST['swimmerInsert'];
  if ($swimmer != null && $swimmer != "") {
    try {
      // Check member belongs to tenant
      $getCount = $db->prepare("SELECT COUNT(*) FROM members WHERE MemberID = ? AND Tenant = ?");
      $getCount->execute([
        $swimmer,
        $tenant->getId()
      ]);

      if ($getCount->fetchColumn() == 0) {
        throw new Exception();
      }

      // Check count
      $getCount = $db->prepare("SELECT COUNT(*) FROM targetedListMembers WHERE ListID = ? AND ReferenceID = ? AND ReferenceType = ?");
      $getCount->execute([
        $id,
        $swimmer,
        'Member'
      ]);

      if ($getCount->fetchColumn() > 0) {
        throw new Exception();
      }

      $insert = $db->prepare("INSERT INTO `targetedListMembers` (`ListID`, `ReferenceID`, `ReferenceType`) VALUES (?, ?, ?)");
      $insert->execute([
        $id,
        $swimmer,
        'Member'
      ]);
    } catch (Exception $e) {
      halt(403);
    }
  }
} else if ($_POST['response'] == "insert-user") {
  $swimmer = $_POST['swimmerInsert'];
  if ($swimmer != null && $swimmer != "") {
    try {
      // Check user belongs to tenant
      $getCount = $db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ? AND Tenant = ?");
      $getCount->execute([
        $swimmer,
        $tenant->getId()
      ]);

      if ($getCount->fetchColumn() == 0) {
        throw new Exception();
      }

      // Check count
      $getCount = $db->prepare("SELECT COUNT(*) FROM targetedListMembers WHERE ListID = ? AND ReferenceID = ? AND ReferenceType = ?");
      $getCount->execute([
        $id,
        $swimmer,
        'User'
      ]);
      if ($getCount->fetchColumn() > 0) {
        throw new Exception();
      }

      $insert = $db->prepare("INSERT INTO `targetedListMembers` (`ListID`, `ReferenceID`, `ReferenceType`) VALUES (?, ?, ?)");
      $insert->execute([
        $id,
        $swimmer,
        'User'
      ]);
    } catch (Exception $e) {
      halt(403);
    }
  }
} else if ($_POST['response'] == "dropRelation") {
  try {
    // Is current tenant
    $getCount = $db->prepare("SELECT COUNT(*) FROM targetedListMembers INNER JOIN targetedLists ON targetedLists.ID = targetedListMembers.ListID WHERE targetedListMembers.ID = ? AND targetedLists.Tenant = ?");
    $getCount->execute([
      $_POST['relation'],
      $tenant->getId()
    ]);

    if ($getCount->fetchColumn() == 0) {
      throw new Exception();
    }

    $drop = $db->prepare("DELETE FROM `targetedListMembers` WHERE `ID` = ?");
    $drop->execute([$_POST['relation']]);
  } catch (Exception $e) {
    halt(403);
  }
} else {
  halt(404);
}
