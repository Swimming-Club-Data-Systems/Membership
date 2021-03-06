<?php

$db = app()->db;

$yes = $no = "";

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$medInfo = $db->prepare("SELECT * FROM `members` LEFT JOIN `memberMedical` ON members.MemberID = memberMedical.MemberID WHERE members.MemberID = ?");
$medInfo->execute([$id]);
$row = $medInfo->fetch(PDO::FETCH_ASSOC);

$name = $row['MForename'] . ' ' . $row['MSurname'];

$pagetitle = htmlspecialchars($name) . " - Medical Review";

if ($row == null) {
	halt(500);
}

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/renewalTitleBar.php";
?>

<div class="container-xl">
	<div class="">
		<form method="post" name="med" id="med">
			<h1>Medical Form for <?=htmlspecialchars($row['MForename'])?></h1>
			<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
				echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
				unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
			} ?>
			<p class="lead">
        Check the details for <?=htmlspecialchars($name)?> are correct.
      </p>

			<div class="mb-2">
				<p class="mb-2">
          Does <?=htmlspecialchars($name)?> have any specific medical conditions
          or disabilities?
      </p>

				<?php if ($row['Conditions'] != "") {
					$yes = " checked ";
					$no = "";
				} else {
					$yes = "";
					$no = " checked ";
				} ?>

				<div class="form-check">
				  <input type="radio" value="0" <?=$no?> id="medConDisNo" name="medConDis" class="form-check-input" onclick="toggleState('medConDisDetails', 'medConDis')">
				  <label class="form-check-label" for="medConDisNo">No</label>
				</div>
				<div class="form-check">
				  <input type="radio" value="1" <?=$yes?> id="medConDisYes" name="medConDis" class="form-check-input" onclick="toggleState('medConDisDetails', 'medConDis')">
				  <label class="form-check-label" for="medConDisYes">Yes</label>
				</div>
			</div>

			<div class="mb-3">
		    <label class="form-label" for="medConDisDetails">If yes give details</label>
		    <textarea class="form-control" id="medConDisDetails" name="medConDisDetails"
		    rows="3" <?php if ($yes == "") { ?>disabled<?php } ?>><?=htmlspecialchars($row['Conditions'])?></textarea>
		  </div>

			<!-- -->

			<div class="mb-2">
				<p class="mb-2">Does <?=htmlspecialchars($name)?> have any allergies?</p>

				<?php if ($row['Allergies'] != "") {
					$yes = " checked ";
					$no = "";
				} else {
					$yes = "";
					$no = " checked ";
				} ?>

				<div class="form-check">
				  <input type="radio" value="0" <?=$no?> id="allergiesNo"
				  name="allergies" class="form-check-input" onclick="toggleState('allergiesDetails', 'allergies')">
				  <label class="form-check-label" for="allergiesNo">No</label>
				</div>
				<div class="form-check">
				  <input type="radio" value="1" <?=$yes?> id="allergiesYes"
				  name="allergies" class="form-check-input" onclick="toggleState('allergiesDetails', 'allergies')">
				  <label class="form-check-label" for="allergiesYes">Yes</label>
				</div>
			</div>

			<div class="mb-3">
		    <label class="form-label" for="allergiesDetails">If yes give details</label>
		    <textarea class="form-control" id="allergiesDetails" name="allergiesDetails"
		    rows="3" <?php if ($yes == "") { ?>disabled<?php } ?>><?=htmlspecialchars($row['Allergies'])?></textarea>
		  </div>

			<!-- -->

			<div class="mb-2">
				<p class="mb-2">Does <?=htmlspecialchars($name)?> take any regular medication?</p>

				<?php if ($row['Medication'] != "") {
					$yes = " checked ";
					$no = "";
				} else {
					$yes = "";
					$no = " checked ";
				} ?>

				<div class="form-check">
				  <input type="radio" value="0" <?=$no?> id="medicineNo" name="medicine" class="form-check-input" onclick="toggleState('medicineDetails', 'medicine')">
				  <label class="form-check-label" for="medicineNo">No</label>
				</div>
				<div class="form-check">
				  <input type="radio" value="1" <?=$yes?> id="medicineYes" name="medicine" class="form-check-input" onclick="toggleState('medicineDetails', 'medicine')">
				  <label class="form-check-label" for="medicineYes">Yes</label>
				</div>
			</div>

			<div class="mb-3">
		    <label class="form-label" for="medConDisDetails">If yes give details</label>
		    <textarea class="form-control" id="medicineDetails" name="medicineDetails"
		    rows="3" <?php if ($yes == "") { ?>disabled<?php } ?>><?=htmlspecialchars($row['Medication'])?></textarea>
		  </div>

			<div>
				<button type="submit" class="btn btn-success">Save and Continue</a>
			</div>
		</form>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/medical-forms/MedicalForm.js");
$footer->render();
