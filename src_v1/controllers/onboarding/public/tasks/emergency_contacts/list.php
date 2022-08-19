<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

$db = app()->db;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$sql = $db->prepare("SELECT `Forename`, `Surname`, `Mobile` FROM `users` WHERE `UserID` = ?");
$sql->execute([$user->getId()]);
$row = $sql->fetch(PDO::FETCH_ASSOC);

$mobile = PhoneNumber::parse($row['Mobile']);

$contacts = new EmergencyContacts($db);
$contacts->byParent($user->getId());

$contactsArray = $contacts->getContacts();

ob_start();

?>

<ul class="list-group mb-3">
  <li class="list-group-item">
    <div class="row align-items-center">
      <div class="col-auto">
        <p class="mb-0">
          <strong>
            <?= htmlspecialchars($row['Forename'] . " " . $row['Surname']) ?>
          </strong>
          <em>(From My Account)</em>
        </p>
        <p class="mb-0">
          <a href="<?= htmlspecialchars($mobile->format(PhoneNumberFormat::RFC3966)) ?>">
            <?= htmlspecialchars($mobile->format(PhoneNumberFormat::NATIONAL)) ?>
          </a>
        </p>
      </div>
    </div>
  </li>
  <?php for ($i = 0; $i < sizeof($contactsArray); $i++) {
  ?>
    <li class="list-group-item">
      <div class="row align-items-center">
        <div class="col-auto">
          <p class="mb-0">
            <strong class="">
              <?= htmlspecialchars($contactsArray[$i]->getName()) ?>
            </strong>
            <em>
              (<?= htmlspecialchars($contactsArray[$i]->getRelation()) ?>)
            </em>
          </p>
          <p class="mb-0">
            <a href="tel:<?= htmlspecialchars($contactsArray[$i]->getRFCContactNumber()) ?>">
              <?= htmlspecialchars($contactsArray[$i]->getNationalContactNumber()) ?>
            </a>
          </p>
        </div>
        <div class="col text-end">
          <div class="btn-group">
            <button type="button" data-action="edit" data-id="<?= htmlspecialchars($contactsArray[$i]->getID()) ?>" data-name="<?= htmlspecialchars($contactsArray[$i]->getName()) ?>" data-relation="<?= htmlspecialchars($contactsArray[$i]->getRelation()) ?>" data-number="<?= htmlspecialchars($contactsArray[$i]->getContactNumber()) ?>" class="btn btn-primary">
              Edit
            </button>
            <button type="button" data-action="delete" data-id="<?= htmlspecialchars($contactsArray[$i]->getID()) ?>" data-name="<?= htmlspecialchars($contactsArray[$i]->getName()) ?>" data-relation="<?= htmlspecialchars($contactsArray[$i]->getRelation()) ?>" data-number="<?= htmlspecialchars($contactsArray[$i]->getContactNumber()) ?>" class="btn btn-danger">
              Delete
            </button>
          </div>
        </div>
      </div>
    </li>
  <?php
  } ?>
</ul>

<?php

$output = ob_get_contents();
ob_end_clean();

header('content-type: application/json');
echo json_encode([
  'html' => $output,
  'count' => sizeof($contactsArray),
]);
