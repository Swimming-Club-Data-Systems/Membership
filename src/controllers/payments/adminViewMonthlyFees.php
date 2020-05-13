<?php

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
if ($access != "Admin") {
	halt(404);
}

$db = app()->db;

$getDetails = $db->query("SELECT `Forename`, `Surname`, `UserID` FROM `users` INNER JOIN `permissions` ON users.UserID = `permissions`.`User` WHERE `Permission` = 'Parent' ORDER BY `Forename` ASC, `Surname` ASC");

$pagetitle = "Administration";
include BASE_PATH . 'views/header.php'; ?>

<div class="container">
	<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments")?>">Payments</a></li>
      <li class="breadcrumb-item active" aria-current="page">Member Fees</li>
    </ol>
  </nav>
	<h1>Member Fees</h1>
	<p class="lead">Fees for all registered parents and members</p>
	<div class="table-responsive-md">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Name</th>
					<th>Swimmers</th>
					<th>Expected Payment</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = $getDetails->fetch(PDO::FETCH_ASSOC)) { ?>
				<tr>
					<td><?=htmlspecialchars($row['Forename'] . ' ' . $row['Surname'])?></td>
					<td><?=swimmers(null, $row['UserID'], true)?></td>
					<td>
						Squads: <?=(monthlyFeeCost(null, $row['UserID'], "string"))?> <br>
						Extras Fees: <?=(monthlyExtraCost(null, $row['UserID'], "string"))?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php
$footer = new \SCDS\Footer();
$footer->render();
