<?php

header("content-type: application/json");

try {
  $db = app()->db;
  $tenant = app()->tenant;

  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Admin' && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Coach') {
    throw new Exception('Incorrect permissions');
  }

  if ($_POST['operation'] == 'delete') {
    $verifyMove = $db->prepare("SELECT * FROM squadMoves INNER JOIN members ON squadMoves.Member = members.MemberID WHERE members.Tenant = ? AND squadMoves.ID = ?");
    $verifyMove->execute([
      $tenant->getId(),
      $_POST['move'],
    ]);

    $move = $verifyMove->fetch(PDO::FETCH_ASSOC);

    if (!$move) {
      throw new Exception('Move does not exist');
    }

    $getParent = $db->prepare("SELECT Forename, Surname, EmailAddress, squadMoves.Old, squadMoves.New, squadMoves.Member FROM ((members INNER JOIN squadMoves ON members.MemberID = squadMoves.Member) INNER JOIN users ON members.UserID = users.UserID) WHERE squadMoves.ID = ?");
    $getParent->execute([
      $_POST['move'],
    ]);

    $deleteMove = $db->prepare("DELETE FROM squadMoves WHERE ID = ?");
    $deleteMove->execute([
      $_POST['move'],
    ]);

    try {
      if ($user = $getParent->fetch(PDO::FETCH_ASSOC)) {
        $member = new Member($user['Member']);
        $leave = $join = null;
        if ($user['Old']) {
          try {
            $leave = Squad::get($user['Old']);
          } catch (Exception $e) {
          }
        }
        if ($user['New']) {
          try {
            $join = Squad::get($user['New']);
          } catch (Exception $e) {
          }
        }
        $subject = $member->getFullName() . '\'s move has been cancelled';
        $message = '<p>Hello ' . htmlspecialchars($user['Forename']) . ',</p><p>We\'re writing to let you know that ' . htmlspecialchars($member->getForename()) . '\'s squad move (';
        if ($leave && $join) {
          $message .= 'from ' . $leave->getName() . ' to ' . $join->getName();
        } else if ($leave) {
          $message .= 'leaving ' . $leave->getName();
        } else if ($join) {
          $message .= 'joining ' . $join->getName();
        }
        $message .= ') has been cancelled.</p>';
        if (!$tenant->getBooleanKey('HIDE_MOVE_FEE_INFO')) {
          $message .= '<p>The fee for ' . htmlspecialchars($join->getName()) . ' is &pound;' . htmlspecialchars($join->getFee(false)) . '.</p>';
        }
        $message .= '<p>If you have any questions, please contact your coach or a member of club staff.</p>';
        $message .= '<p>Kind Regards,<br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';
        notifySend(null, $subject, $message, $user['Forename'] . ' ' . $user['Surname'], $user['EmailAddress']);
      }
    } catch (Exception $e) {
      // Email unsent
      reportError($e);
    }

    echo (json_encode([
      'status' => 200,
      'success' => true,
    ]));
  } else {
    throw new Exception('Unsupported operation');
  }
} catch (PDOException $e) {
  echo (json_encode([
    'status' => 200,
    'success' => false,
    'error_message' => 'A database error occurred. Please try again later',
  ]));
} catch (Exception $e) {
  echo (json_encode([
    'status' => 200,
    'success' => false,
    'error_message' => $e->getMessage(),
  ]));
}
