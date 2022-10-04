<?php

$db = app()->db;
$tenant = app()->tenant;

$user = null;

$count = $db->prepare("SELECT COUNT(*) FROM notifyAdditionalEmails INNER JOIN users ON users.UserID = notifyAdditionalEmails.UserID WHERE `ID` = ? AND `Hash` = ? AND users.Tenant = ?");
$count->execute([
  $id,
  $hash,
  $tenant->getId()
]);

if ($count->fetchColumn() > 0) {
  $done = false;
  $verified = $db->prepare("SELECT Verified, UserID FROM notifyAdditionalEmails WHERE `ID` = ? AND `Hash` = ?");
  $verified->execute([
    $id,
    $hash
  ]);

  $verif = $verified->fetch(PDO::FETCH_ASSOC);
  $user = $db->prepare("SELECT Forename fn, Surname sn FROM users WHERE UserID = ?");
  $user->execute([
    $verif['UserID']
  ]);
  $user = $user->fetch(PDO::FETCH_ASSOC);

  if ($verif['Verified']) {
    $done = true;
  } else {
    $update = $db->prepare("UPDATE notifyAdditionalEmails SET Verified = ? WHERE ID = ?");
    $update->bindValue(1, true, PDO::PARAM_BOOL);
    $update->bindValue(2, $id, PDO::PARAM_INT);
    $update->execute();
  }
} else {
  halt(404);
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <?php if ($done) { ?>
      <h1>Email already verified</h1>
      <p class="lead">Your email address has already been verified</p>
      <?php } else { ?>
      <h1>Email verified</h1>
      <p class="lead">Thanks for verifying your email address</p>
      <?php } ?>

      <p>You will receive copies of all email updates from coaches and committee members sent to <?=htmlspecialchars($user['fn'] . ' ' . $user['sn'])?>.</p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();