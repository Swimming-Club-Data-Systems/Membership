<?php

$db = app()->db;
$tenant = app()->tenant;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Pending Messages";
$use_white_background = true;

$mails = $db->prepare("SELECT users.UserID, EmailID, Forename, Surname, notify.Subject PSubject, notifyHistory.Subject HSubject FROM ((`notify` INNER JOIN `users` ON notify.UserID = users.UserID) LEFT JOIN notifyHistory ON notify.MessageID = notifyHistory.ID) WHERE `Status` = 'Queued' AND users.Tenant = ?");
$mails->execute([
	$tenant->getId()
]);
$mail = $mails->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/notifyMenu.php";

 ?>

<div class="container-xl">
	<h1>Pending Messages</h1>
	<p class="lead">Messages listed here are queued to be sent in batches.</p>
	<?php if ($mail != null) { ?>
		<div class="table-responsive-md">
			<table class="table table-light">
				<thead">
					<tr>
						<th>Name</th>
						<th>Subject</th>
					</tr>
				</thead>
				<tbody>
				<?php do { ?>
					<tr>
						<td>
							<a href="<?=autoUrl("users/" . $mail['UserID'])?>" title="Information about <?=htmlspecialchars($mail['Forename'] . " " . $mail['Surname'])?>">
								<?=htmlspecialchars($mail['Forename'] . " " . $mail['Surname'])?>
							</a>
						</td>
						<td><?php
							$subject = $mail['PSubject'];
							if ($mail['PSubject'] == null) {
								$subject = $mail['HSubject'];
							} ?>
							<a href="<?=autoUrl("notify/email/" . $mail['EmailID'])?>" title="View <?=htmlspecialchars($subject)?>">
								<?=htmlspecialchars($subject)?>
							</a>
						</td>
					</tr>
				<?php } while ($mail = $mails->fetch(PDO::FETCH_ASSOC)); ?>
			</tbody>
		</table>
	</div>
	<?php } else { ?>
	<div class="alert alert-info">
		There are no messages in the queue to send.
	</div>
	<?php } ?>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
