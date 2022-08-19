<?php

$db = app()->db;

use function GuzzleHttp\json_encode;

header("content-type: application/json");

try {

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('CSRF validation failure');
  }

  try {
    $member = new Member($_POST['member']);
  } catch (Exception $e) {
    throw new Exception('No such member under this tenant');
  }
  $tenant = app()->tenant;

  // Handle move logic
  if ($_POST['event'] == 'move') {
    // Verify squad
    $squadCount = $db->prepare("SELECT COUNT(*) FROM squads WHERE squads.SquadID = ? AND squads.Tenant = ?");
    $squadCount->execute([
      $_POST['leave'],
      $tenant->getId()
    ]);

    if ($squadCount->fetchColumn() == 0) {
      throw new Exception('Old squad does not exist at this tenant');
    }

    $squadCount->execute([
      $_POST['join'],
      $tenant->getId()
    ]);

    if ($squadCount->fetchColumn() == 0) {
      throw new Exception('New squad does not exist at this tenant');
    }

    // Check member is in leaving squad/not in new squad
    $getMembershipCount = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad = ?");

    // In leaving
    $getMembershipCount->execute([
      $member->getId(),
      $_POST['leave'],
    ]);
    if ($getMembershipCount->fetchColumn() == 0) {
      throw new Exception('Member is not in the squad they are leaving');
    }

    // Not in new
    $getMembershipCount->execute([
      $member->getId(),
      $_POST['join'],
    ]);
    if ($getMembershipCount->fetchColumn() != 0) {
      throw new Exception('Member is already in the squad you have selected to add them to');
    }

    // Check there are no existing moves
    $getCount = $db->prepare("SELECT COUNT(*) FROM squadMoves WHERE Member = :member AND (Old = :old_squad OR New = :old_squad OR Old = :new_squad OR New = :new_squad)");
    $getCount->execute([
      'member' => $_POST['member'],
      'old_squad' => $_POST['leave'],
      'new_squad' => $_POST['join'],
    ]);

    if ($getCount->fetchColumn() > 0) {
      throw new Exception('An existing squad move is already in place for this member and one or more of the squads selected for this new move. Cancel it first to make changes.');
    }

    // Proceed
    $statusMessage = null;
    if (bool($_POST['move-when'])) {
      // Move on specified date
      $date = new DateTime('now', new DateTimeZone('Europe/London'));
      try {
        $date = new DateTime($_POST['move-date'], new DateTimeZone('Europe/London'));
      } catch (Exception $e) {
        // Date invalid
        throw new Exception('The date you provided is invalid');
      }

      // Add move to database
      $add = $db->prepare("INSERT INTO squadMoves (`Member`, `Date`, `Old`, `New`, `Paying`) VALUES (?, ?, ?, ?, ?)");
      $add->execute([
        $member->getId(),
        $date->format("Y-m-d"),
        $_POST['leave'],
        $_POST['join'],
        (int) !bool($_POST['paying']),
      ]);

      // Prepare an email to the parent
      // Get squad info
      $getParent = $db->prepare("SELECT Forename, Surname, EmailAddress FROM members INNER JOIN users ON members.UserID = users.UserID WHERE members.MemberID = ?");
      $getParent->execute([
        $member->getId(),
      ]);
      if ($user = $getParent->fetch(PDO::FETCH_ASSOC)) {
        $leave = Squad::get($_POST['leave']);
        $join = Squad::get($_POST['join']);
        $subject = $member->getFullName() . ' is moving to ' . $join->getName();
        $message = '<p>Hello ' . htmlspecialchars($user['Forename']) . ',</p><p>We\'re delighted to let you know that ' . htmlspecialchars($member->getForename()) . ' will be moving to ' . htmlspecialchars($join->getName()) . ' and leaving ' . htmlspecialchars($leave->getName()) . ' on ' . htmlspecialchars($date->format("l j F Y")) . '.</p>';
        $message .= '<p>The fee for ' . htmlspecialchars($join->getName()) . ' is &pound;' . htmlspecialchars($join->getFee(false)) . '.</p>';
        $message .= '<p>If you have any questions, please contact your coach or a member of club staff.</p>';
        $message .= '<p>Kind Regards,<br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';
        notifySend(null, $subject, $message, $user['Forename'] . ' ' . $user['Surname'], $user['EmailAddress']);
      }
      $statusMessage = 'We\'ve scheduled the squad move for ' . $member->getForename() . '.';
    } else {
      // Move right now
      $move = $db->prepare("UPDATE squadMembers SET Squad = ?, Paying = ? WHERE Member = ? AND Squad = ?");
      $move->execute([
        $_POST['join'],
        (int) !bool($_POST['paying']),
        $member->getId(),
        $_POST['leave'],
      ]);
      $statusMessage = 'We\'ve moved ' . $member->getForename() . ' to their new squad.';
    }

    echo json_encode([
      'status' => 200,
      'success' => true,
      'message' => $statusMessage,
    ]);
  } else if ($_POST['event'] == 'join') {
    // Verify squad
    $squadCount = $db->prepare("SELECT COUNT(*) FROM squads WHERE squads.SquadID = ? AND squads.Tenant = ?");
    $squadCount->execute([
      $_POST['join'],
      $tenant->getId()
    ]);

    if ($squadCount->fetchColumn() == 0) {
      throw new Exception('New squad does not exist at this tenant');
    }

    // Check member is not in new squad
    $getMembershipCount = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad = ?");

    // Not in new
    $getMembershipCount->execute([
      $member->getId(),
      $_POST['join'],
    ]);
    if ($getMembershipCount->fetchColumn() != 0) {
      throw new Exception('Member is already in the you have selected to add them to');
    }

    // Check there are no existing moves
    $getCount = $db->prepare("SELECT COUNT(*) FROM squadMoves WHERE Member = :member AND (Old = :new_squad OR New = :new_squad)");
    $getCount->execute([
      'member' => $_POST['member'],
      'new_squad' => $_POST['join'],
    ]);

    if ($getCount->fetchColumn() > 0) {
      throw new Exception('An existing squad move is already in place for this member the squad selected for this new move. Cancel it first to make changes.');
    }

    // Proceed
    $statusMessage = null;
    if (bool($_POST['move-when'])) {
      // Move on specified date
      $date = new DateTime('now', new DateTimeZone('Europe/London'));
      try {
        $date = new DateTime($_POST['move-date'], new DateTimeZone('Europe/London'));
      } catch (Exception $e) {
        // Date invalid
        throw new Exception('The date you provided is invalid');
      }

      // Add move to database
      $add = $db->prepare("INSERT INTO squadMoves (`Member`, `Date`, `Old`, `New`, `Paying`) VALUES (?, ?, ?, ?, ?)");
      $add->execute([
        $member->getId(),
        $date->format("Y-m-d"),
        null,
        $_POST['join'],
        (int) !bool($_POST['paying']),
      ]);

      // Prepare an email to the parent
      // Get squad info
      $getParent = $db->prepare("SELECT Forename, Surname, EmailAddress FROM members INNER JOIN users ON members.UserID = users.UserID WHERE members.MemberID = ?");
      $getParent->execute([
        $member->getId(),
      ]);
      if ($user = $getParent->fetch(PDO::FETCH_ASSOC)) {
        $join = Squad::get($_POST['join']);
        $subject = $member->getFullName() . ' is joining ' . $join->getName();
        $message = '<p>Hello ' . htmlspecialchars($user['Forename']) . ',</p><p>We\'re delighted to let you know that ' . htmlspecialchars($member->getForename()) . ' will be joining ' . htmlspecialchars($join->getName()) . ' on ' . htmlspecialchars($date->format("l j F Y")) . '.</p>';
        $message .= '<p>The fee for ' . htmlspecialchars($join->getName()) . ' is &pound;' . htmlspecialchars($join->getFee(false)) . '.</p>';
        $message .= '<p>If you have any questions, please contact your coach or a member of club staff.</p>';
        $message .= '<p>Kind Regards,<br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';
        notifySend(null, $subject, $message, $user['Forename'] . ' ' . $user['Surname'], $user['EmailAddress']);
      }
      $statusMessage = 'We\'ve scheduled ' . $member->getForename() . ' joining their new squad.';
    } else {
      // Move right now
      $move = $db->prepare("INSERT INTO squadMembers (Squad, Member, Paying) VALUES (?, ?, ?)");
      $move->execute([
        $_POST['join'],
        $member->getId(),
        (int) !bool($_POST['paying'])
      ]);
      $statusMessage = 'We\'ve added ' . $member->getForename() . ' to this squad.';
    }

    echo json_encode([
      'status' => 200,
      'success' => true,
      'message' => $statusMessage,
    ]);
  } else if ($_POST['event'] == 'leave') {
    // Leave a squad

    // Verify squad
    $getCount = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad = ?");
    $getCount->execute([
      $_POST['member'],
      $_POST['leave'],
    ]);

    if ($getCount->fetchColumn() == 0) {
      throw new Exception('The member is not an existing member of the squad');
    }

    // Check there are no existing moves
    $getCount = $db->prepare("SELECT COUNT(*) FROM squadMoves WHERE Member = :member AND (Old = :squad OR New = :squad)");
    $getCount->execute([
      'member' => $_POST['member'],
      'squad' => $_POST['leave'],
    ]);

    if ($getCount->fetchColumn() > 0) {
      throw new Exception('An existing squad move is already in place for this squad and member. Cancel it first to make changes.');
    }

    $statusMessage = null;
    if (!bool($_POST['move-when'])) {
      // Remove now
      $move = $db->prepare("DELETE FROM squadMembers WHERE Member = ? AND Squad = ?");
      $move->execute([
        $_POST['member'],
        $_POST['leave']
      ]);
      $statusMessage = 'We\'ve removed ' . $member->getForename() . ' from that squad.';
    } else {
      // Move on specified date
      $date = new DateTime('now', new DateTimeZone('Europe/London'));
      try {
        $date = new DateTime($_POST['move-date'], new DateTimeZone('Europe/London'));
      } catch (Exception $e) {
        // Date invalid
        throw new Exception('The date you provided is invalid');
      }

      // Add move to database
      $add = $db->prepare("INSERT INTO squadMoves (`Member`, `Date`, `Old`, `New`, `Paying`) VALUES (?, ?, ?, ?, ?)");
      $add->execute([
        $member->getId(),
        $date->format("Y-m-d"),
        $_POST['leave'],
        null,
        0,
      ]);

      // Prepare an email to the parent
      // Get squad info
      $getParent = $db->prepare("SELECT Forename, Surname, EmailAddress FROM members INNER JOIN users ON members.UserID = users.UserID WHERE members.MemberID = ?");
      $getParent->execute([
        $member->getId(),
      ]);
      if ($user = $getParent->fetch(PDO::FETCH_ASSOC)) {
        $leave = Squad::get($_POST['leave']);
        $subject = $member->getFullName() . ' is leaving ' . $leave->getName();
        $message = '<p>Hello ' . htmlspecialchars($user['Forename']) . ',</p><p>We\'re writing to let you know that ' . htmlspecialchars($member->getForename()) . ' will be leaving ' . htmlspecialchars($leave->getName()) . ' on ' . htmlspecialchars($date->format("l j F Y")) . '.</p>';
        $message .= '<p>If you have any questions, please contact your coach or a member of club staff.</p>';
        $message .= '<p>Kind Regards,<br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';
        notifySend(null, $subject, $message, $user['Forename'] . ' ' . $user['Surname'], $user['EmailAddress']);
      }

      $statusMessage = 'We\'ve scheduled ' . $member->getForename() . '\'s removal from that squad.';
    }

    echo json_encode([
      'status' => 200,
      'success' => true,
      'message' => $statusMessage,
    ]);
  }
} catch (Exception $e) {

  $message = $e->getMessage();
  if (get_class($e) == 'PDOException') {
    $message = 'A database error occurred.';
  }

  http_response_code(200);
  echo json_encode([
    'status' => 200,
    'success' => false,
    'error' => $e->getMessage(),
    'message' => null,
  ]);
}
