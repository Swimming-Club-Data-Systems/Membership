<?php

$db = app()->db;
$tenant = app()->tenant;

$date = new DateTime('now', new DateTimeZone('Europe/London'));

$name = $currentSquad = "";

$getSquads = null;

$getMemberInfo = $db->prepare("SELECT `MForename`, `MSurname`, `SquadName`, members.SquadID FROM `members` INNER JOIN `squads` ON members.SquadID = squads.SquadID WHERE `MemberID` = ? AND Tenant = ?");
$getMemberInfo->execute([
	$id,
	$tenant->getId()
]);

$member = $getMemberInfo->fetch(PDO::FETCH_ASSOC);

if ($member == null) {
	halt(404);
}

$name = $member['MForename'] . " " . $member['MSurname'];
$currentSquad = $member['SquadName'];
$squadID = $member['SquadID'];

$getSquads = $db->prepare("SELECT `SquadName`, `SquadID` FROM `squads` WHERE `SquadID` != ? AND Tenant = ? ORDER BY `SquadFee` DESC, `SquadName` ASC");
$getSquads->execute([
	$squadID,
	$tenant->getId()
]);
$squad = $getSquads->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Squad Move for " . htmlspecialchars($name);
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/squadMenu.php"; ?>
<div class="container-xl">
	<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("squads/moves")?>">Squad Moves</a></li>
      <li class="breadcrumb-item active" aria-current="page">New - <?=htmlspecialchars($member['MForename'] . " " . $member['MSurname'][0])?></li>
    </ol>
  </nav>
	<div class="">
		<h1>Squad Move for <?=htmlspecialchars($name)?></h1>
		<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
			echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
			unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
		} ?>
		<form method="post">
			<div class="mb-3 row">
		    <label class="form-label" for="swimmerName" class="col-sm-2 col-form-label">Swimmer</label>
		    <div class="col-sm-10">
		      <input type="text" readonly class="form-control" id="swimmerName" name="swimmerName" value="<?=htmlspecialchars($name)?>" disabled>
		    </div>
		  </div>
			<div class="mb-3 row">
		    <label class="form-label" for="currentSquad" class="col-sm-2 col-form-label">Current Squad</label>
		    <div class="col-sm-10">
		      <input type="text" readonly class="form-control" id="currentSquad" name="currentSquad" value="<?=htmlspecialchars($currentSquad)?>" disabled>
		    </div>
		  </div>
		  <div class="mb-3 row">
		    <label class="form-label" for="newSquad" class="col-sm-2 col-form-label">New Squad</label>
		    <div class="col-sm-10">
					<select class="form-select" id="newSquad" name="newSquad">
						<!-- HIDE CURRENT SQUAD AS POINTLESS -->
				    <option selected>Choose...</option>
						<?php do { ?>
							<option value="<?=$squad['SquadID']?>"><?=htmlspecialchars($squad['SquadName'])?></option>
						<?php } while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)); ?>
				  </select>
		    </div>
		  </div>
			<div class="mb-3 row">
		    <label class="form-label" for="movingDate" class="col-sm-2 col-form-label">Moving Date</label>
		    <div class="col-sm-10">
		      <input type="date" class="form-control" id="movingDate" name="movingDate" min="<?=htmlspecialchars($date->format("Y-m-d"))?>" value="<?=htmlspecialchars($date->format("Y-m-d"))?>" aria-describedby="dateHelper">
          <small id="dateHelper" class="form-text text-muted">
            Pick a date that is either now or in the future.
          </small>
		    </div>
		  </div>
			<button type="submit" class="btn btn-dark-l btn-outline-light-d">Save Move</button>
		</form>
	</div>
</div>
<?php $footer = new \SCDS\Footer();
$footer->render();
