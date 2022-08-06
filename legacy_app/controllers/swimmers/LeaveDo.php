<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$leavers = config('LeaversSquad');
$query = $db->prepare("SELECT UserID, MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
$query->execute([
  $id,
  $tenant->getId()
]);
$row = $query->fetch(PDO::FETCH_ASSOC);

if ($row == null || $row['UserID'] != $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID']) {
  halt(404);
}

$query = $db->prepare("SELECT COUNT(*) FROM moves WHERE MemberID = ?");
$query->execute([$id]);
$count = $query->fetchColumn();

if ($count != 0) {
  halt(500);
}

if ($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['LeaveKey'] == $key) {
  try {
    $query = $db->prepare("INSERT INTO moves (MemberID, SquadID, MovingDate) VALUES (?, ?, ?)");
    $query->execute([$id, $leavers, date("Y-m-01", strtotime('+1 month'))]);

    // Notify the parent
		$sql = "INSERT INTO `notify` (`UserID`, `Status`, `Subject`, `Message`, `ForceSend`, `EmailType`) VALUES (?, ?, ?, ?, ?, ?)";
		$notify_query = $db->prepare($sql);

    $date = date("Y-m-01", strtotime('+1 month'));
    $subject = $row['MForename'] . ' ' . $row['MSurname'] . ' is leaving ' . config('CLUB_NAME');
    $message = '<p>We\'re sorry to see you go.</p>';
    $message .= '<p>' . htmlspecialchars($row['MForename'] . ' ' . $row['MSurname']) . ' will be removed from our computer systems on ' . date("l j F Y", strtotime($date)) . '.</p>';
    $message .= '<p>They will not be allowed to take part in any training sessions on or after this date. If you think this was a mistake, please contact the membership secretary.</p>';
    $message .= '<p>Kind regards,<br>The ' . htmlspecialchars(config('CLUB_NAME')) . ' Team</p>';

    $notify_query->execute([
      $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'],
      'Queued',
      $subject,
      $message,
      1,
      'ClubLeaver'
    ]);

    unset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['LeaveKey']);
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['ConfirmLeave'] = true;
    header("Location: " . autoUrl("members/" . $id . "/leaveclub/"));
  } catch (Exception $e) {
    halt(500);
  }
} else {
  unset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['LeaveKey']);
  halt(404);
}
