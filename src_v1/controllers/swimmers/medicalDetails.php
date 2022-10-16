<?php

$db = app()->db;
$tenant = app()->tenant;

$yes = $no = "";

$getMed;

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
	$getMed = $db->prepare("SELECT MForename, MSurname, Conditions, Allergies,
  Medication, `GPName`, `GPAddress`, `GPPhone`, `WithholdConsent` FROM `members` LEFT JOIN `memberMedical` ON members.MemberID =
  memberMedical.MemberID WHERE members.Tenant = ? AND members.MemberID = ? AND members.UserID = ?");
	$getMed->execute([
		$tenant->getId(),
		$id,
		$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
	]);
} else {
	$getMed = $db->prepare("SELECT MForename, MSurname, Conditions, Allergies,
  Medication, `GPName`, `GPAddress`, `GPPhone`, `WithholdConsent` FROM `members` LEFT JOIN `memberMedical` ON members.MemberID =
  memberMedical.MemberID WHERE members.Tenant = ? AND members.MemberID = ?");
	$getMed->execute([
		$tenant->getId(),
		$id
	]);
}

$row = $getMed->fetch(PDO::FETCH_ASSOC);

$member = new Member($id);
$user = $member->getUser();

if ($row == null) {
	halt(404);
}

$pagetitle = "Medical Review - " . htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname']));

include BASE_PATH . "views/header.php";
?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-xl">

		<!-- Page header -->
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= autoUrl("members") ?>">Members</a></li>
				<li class="breadcrumb-item"><a href="<?= autoUrl("members/" . $id) ?>">#<?= htmlspecialchars($id) ?></a></li>
				<li class="breadcrumb-item active" aria-current="page">Medical Form</li>
			</ol>
		</nav>

		<div class="row align-items-center">
			<div class="col-lg-8">
				<h1>
					Medical Form
				</h1>
				<p class="lead mb-0" id="leadDesc">
					Check the details for <?= htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname'])) ?> are correct.
				</p>
			</div>
		</div>
	</div>
</div>

<div class="container-xl">

	<div class="row">
		<div class="col-lg-4 order-1 order-lg-2">
			<div class="card card-body mb-3 position-sticky top-3">
				<p class="mb-0">
					<strong>
						<a href="https://www.markdownguide.org/" target="_blank">Formatting with Markdown</a> is supported in these forms.
					</strong>
				</p>
				<p>
					To start a new line, press return twice.
				</p>
				<p class="mb-0">
					For a bulleted list do the following;
				</p>
				<pre class="mb-0"><code>
* first item in list
* second item in list
</code></pre>
			</div>
		</div>
		<div class="col-lg-8 order-2 order-lg-1">

			<form method="post" action="<?= htmlspecialchars(autoUrl("members/" . $id . "/medical")) ?>" name="med" id="med">
				<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
					echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
					unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
				} ?>

				<div class="mb-2">
					<p class="mb-2">Does <?= htmlspecialchars($row['MForename']) ?> have any specific medical conditions
						or disabilities?</p>

					<?php if ($row['Conditions'] != "") {
						$yes = " checked ";
						$no = "";
					} else {
						$yes = "";
						$no = " checked ";
					} ?>

					<div class="form-check">
						<input type="radio" value="0" <?= $no ?> id="medConDisNo" name="medConDis" class="form-check-input" onclick="toggleState('medConDisDetails', 'medConDis')">
						<label class="form-check-label" for="medConDisNo">No</label>
					</div>
					<div class="form-check">
						<input type="radio" value="1" <?= $yes ?> id="medConDisYes" name="medConDis" class="form-check-input" onclick="toggleState('medConDisDetails', 'medConDis')">
						<label class="form-check-label" for="medConDisYes">Yes</label>
					</div>
				</div>

				<div class="mb-3">
					<label class="form-label" for="medConDisDetails">If yes give details</label>
					<textarea oninput="autoGrow(this)" class="form-control auto-grow" id="medConDisDetails" name="medConDisDetails" rows="8" <?php if ($yes == "") { ?>disabled<?php } ?>><?= htmlspecialchars($row['Conditions']) ?></textarea>
				</div>

				<!-- -->

				<div class="mb-2">
					<p class="mb-2">Does <?= htmlspecialchars($row['MForename']) ?> have any allergies?</p>

					<?php if ($row['Allergies'] != "") {
						$yes = " checked ";
						$no = "";
					} else {
						$yes = "";
						$no = " checked ";
					} ?>

					<div class="form-check">
						<input type="radio" value="0" <?= $no ?> id="allergiesNo" name="allergies" class="form-check-input" onclick="toggleState('allergiesDetails', 'allergies')">
						<label class="form-check-label" for="allergiesNo">No</label>
					</div>
					<div class="form-check">
						<input type="radio" value="1" <?= $yes ?> id="allergiesYes" name="allergies" class="form-check-input" onclick="toggleState('allergiesDetails', 'allergies')">
						<label class="form-check-label" for="allergiesYes">Yes</label>
					</div>
				</div>

				<div class="mb-3">
					<label class="form-label" for="allergiesDetails">If yes give details</label>
					<textarea oninput="autoGrow(this)" class="form-control auto-grow" id="allergiesDetails" name="allergiesDetails" rows="8" <?php if ($yes == "") { ?>disabled<?php } ?>><?= htmlspecialchars($row['Allergies']) ?></textarea>
				</div>

				<!-- -->

				<div class="mb-2">
					<p class="mb-2">Does <?= htmlspecialchars($row['MForename']) ?> take any regular medication?</p>

					<?php if ($row['Medication'] != "") {
						$yes = " checked ";
						$no = "";
					} else {
						$yes = "";
						$no = " checked ";
					} ?>

					<div class="form-check">
						<input type="radio" value="0" <?= $no ?> id="medicineNo" name="medicine" class="form-check-input" onclick="toggleState('medicineDetails', 'medicine')">
						<label class="form-check-label" for="medicineNo">No</label>
					</div>
					<div class="form-check">
						<input type="radio" value="1" <?= $yes ?> id="medicineYes" name="medicine" class="form-check-input" onclick="toggleState('medicineDetails', 'medicine')">
						<label class="form-check-label" for="medicineYes">Yes</label>
					</div>
				</div>

				<div class="mb-3">
					<label class="form-label" for="medConDisDetails">If yes give details</label>
					<textarea oninput="autoGrow(this)" class="form-control auto-grow" id="medicineDetails" name="medicineDetails" rows="8" <?php if ($yes == "") { ?>disabled<?php } ?>><?= htmlspecialchars($row['Medication']) ?></textarea>
				</div>

				<?php if ($member->getAge() < 18 && $user) { ?>
					<h2>Consent for emergency medical treatment</h2>
					<p>
						It may be essential at some time for the club to have the necessary authority to obtain any urgent medical treatment for <?= htmlspecialchars($member->getForename()) ?> whilst they train, compete or take part in activities with <?= htmlspecialchars(app()->tenant->getName()) ?>.
					</p>

					<p>
						If you wish to grant such authority, please complete the details below to give your consent.
					</p>

					<p>
						I, <?= htmlspecialchars($user->getFullName()) ?> being the parent/guardian of <?= htmlspecialchars($member->getFullName()) ?> hereby consent to the use of this information by <?= htmlspecialchars($tenant->getName()) ?> for the protection and safeguarding of my child’s health. I also give permission for the Coach, Team Manager or other Club Officer to give the immediate necessary authority on my behalf for any medical or surgical treatment recommended by competent medical authorities, where it would be contrary to my <?= htmlspecialchars($member->getFullName()) ?>'s interest, in the doctor’s medical opinion, for any delay to be incurred by seeking my personal consent.
					</p>

					<p>
						I understand that <?= htmlspecialchars($tenant->getName()) ?> may still have a lawful need to use this information for such purposes even if I later seek to withdraw this consent.
					</p>

					<p>
						<?= htmlspecialchars($tenant->getName()) ?> will use your personal data for the purpose of <?= htmlspecialchars($member->getFullName()) ?>'s involvement in training, activities or competitions with <?= htmlspecialchars($tenant->getName()) ?>.
					</p>

					<p>
						For further details of how we process your personal data or your child’s personal data please <a href="<?= htmlspecialchars(autoUrl('privacy')) ?>" target="_blank">view our Privacy Policy</a> (opens in new tab).
					</p>

					<div class="mb-3">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" value="1" id="emergency-medical-auth" name="emergency-medical-auth" <?php if (isset($row['WithholdConsent']) && !bool($row['WithholdConsent'])) { ?>checked<?php } ?>>
							<label class="form-check-label" for="emergency-medical-auth">
								I, <?= htmlspecialchars($user->getFullName()) ?> consent and grant such authority
							</label>
						</div>
					</div>

					<div class="mb-3">
						<label for="gp-name" class="form-label">Name of GP</label>
						<input type="text" class="form-control" id="gp-name" name="gp-name" <?php if (isset($row['GPName'])) { ?>value="<?= htmlspecialchars($row['GPName']) ?>" <?php } ?>>
					</div>

					<?php

					$address = "";
					if ($row['GPAddress']) {
						$data = json_decode($row['GPAddress']);
						for ($i = 0; $i < sizeof($data); $i++) {
							$address .= $data[$i] . "\r\n";
						}
						$address = trim($address);
					}
					?>

					<div class="mb-3">
						<label for="gp-address" class="form-label">Address</label>
						<textarea id="gp-address" name="gp-address" class="form-control" rows="5"><?= htmlspecialchars($address) ?></textarea>
					</div>

					<div class="mb-3">
						<label for="gp-phone" class="form-label">GP telephone number</label>
						<input type="tel" class="form-control" id="gp-phone" name="gp-phone" <?php if (isset($row['GPPhone'])) { ?>value="<?= htmlspecialchars($row['GPPhone']) ?>" <?php } ?>>
					</div>
				<?php } ?>

				<?= SCDS\CSRF::write() ?>

				<div>
					<p>
						<button type="submit" class="btn btn-success">Save</button>
					</p>
				</div>
			</form>
		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/medical-forms/MedicalForm.js");
$footer->render();
