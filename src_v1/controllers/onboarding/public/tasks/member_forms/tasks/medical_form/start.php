<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Member::stagesOrder();

// Get member
$onboardingMember = \SCDS\Onboarding\Member::retrieveById($id);

$member = $onboardingMember->getMember();

$getMedical = $db->prepare("SELECT `Conditions`, `Allergies`, `Medication`, `GPName`, `GPAddress`, `GPPhone`, `WithholdConsent` FROM memberMedical WHERE MemberID = ?");
$getMedical->execute([
  $member->getId(),
]);

$row = $getMedical->fetch(PDO::FETCH_ASSOC);

$pagetitle = 'Medical Form - ' . htmlspecialchars($member->getFullName()) . ' - Onboarding';

include BASE_PATH . "views/head.php";

?>

<div class="min-vh-100 mb-n3 overflow-auto" s>
  <div class="bg-light">
    <div class="container">
      <div class="row justify-content-center py-5">
        <div class="col-lg-8 col-md-10">

          <?php if ($logos) { ?>
            <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75.png')) ?>" srcset="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@2x.png')) ?> 2x, <?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75@3x.png')) ?> 3x" alt="" class="img-fluid d-block mx-auto">
          <?php } else { ?>
            <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/scds.png')) ?>" height="75" width="75" alt="" class="img-fluid d-block mx-auto">
          <?php } ?>

        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row justify-content-center py-5">
      <div class="col-lg-8 col-md-10">
        <h1 class="text-center">Medical Form</h1>

        <p class="lead mb-5 text-center">
          Provide medical details for <?= htmlspecialchars($member->getFullName()) ?>.
        </p>

        <?php if ($member->getAge() < 18) { ?>
          <h2>Medical information</h2>
        <?php } ?>

        <p>
          Please detail below any important medical information that we need to know. This includes any allergies, medical conditions e.g. asthma, epilepsy, diabetes, any current medication, special dietary requirements and/or any injuries.
        </p>

        <form method="post">
          <div class="d-none mb-3">
            <p class="mb-0">
              <strong>
                <a href="https://www.markdownguide.org/" target="_blank" class="">Formatting with Markdown</a> is supported in these forms.
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

          <div class="mb-2">
            <p class="mb-2">Does <?= htmlspecialchars($member->getForename()) ?> have any specific medical conditions
              or disabilities?</p>

            <?php if (isset($row['Conditions']) && $row['Conditions']) {
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
            <textarea oninput="autoGrow(this)" class="form-control auto-grow" id="medConDisDetails" name="medConDisDetails" rows="8" <?php if ($yes == "") { ?>disabled<?php } ?>><?php if (isset($row['Conditions'])) { ?><?= htmlspecialchars($row['Conditions']) ?><?php } ?></textarea>
          </div>

          <!-- -->

          <div class="mb-2">
            <p class="mb-2">Does <?= htmlspecialchars($member->getForename()) ?> have any allergies?</p>

            <?php if (isset($row['Allergies']) && $row['Allergies']) {
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
            <textarea oninput="autoGrow(this)" class="form-control auto-grow" id="allergiesDetails" name="allergiesDetails" rows="8" <?php if ($yes == "") { ?>disabled<?php } ?>><?php if (isset($row['Allergies'])) { ?><?= htmlspecialchars($row['Allergies']) ?><?php } ?></textarea>
          </div>

          <!-- -->

          <div class="mb-2">
            <p class="mb-2">Does <?= htmlspecialchars($member->getForename()) ?> take any regular medication?</p>

            <?php if (isset($row['Medication']) && $row['Medication']) {
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
            <textarea oninput="autoGrow(this)" class="form-control auto-grow" id="medicineDetails" name="medicineDetails" rows="8" <?php if ($yes == "") { ?>disabled<?php } ?>><?php if (isset($row['Medication'])) { ?><?= htmlspecialchars($row['Medication']) ?><?php } ?></textarea>
          </div>

          <?php if ($member->getAge() < 18) { ?>
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
                <input class="form-check-input" type="checkbox" value="1" id="emergency-medical-auth" name="emergency-medical-auth" <?php if (isset($row['WithholdConsent']) && !$row['WithholdConsent']) { ?>checked<?php } ?>>
                <label class="form-check-label" for="emergency-medical-auth">
                  I, <?= htmlspecialchars($user->getFullName()) ?> consent and grant such authority
                </label>
              </div>
            </div>

            <div class="mb-3">
              <label for="gp-name" class="form-label">Name of GP</label>
              <input type="text" class="form-control" id="gp-name" name="gp-name" <?php if (isset($row['GPName'])) { ?>value="<?= htmlspecialchars($row['GPName']) ?>"<?php } ?>>
            </div>

            <?php

            $address = "";
            if ($row['GPAddress']) {
              $data = json_decode($row['GPAddress']);
              for ($i=0; $i < sizeof($data); $i++) { 
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
              <input type="tel" class="form-control" id="gp-phone" name="gp-phone" <?php if (isset($row['GPPhone'])) { ?>value="<?= htmlspecialchars($row['GPPhone']) ?>"<?php } ?>>
            </div>
          <?php } ?>

          <?= SCDS\CSRF::write() ?>

          <p>
            <button type="submit" class="btn btn-success">Confirm</button>
          </p>

        </form>

      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJS("js/medical-forms/MedicalForm.js");
$footer->render();
