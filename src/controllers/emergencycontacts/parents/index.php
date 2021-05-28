<?php

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$db = app()->db;

if (isset($renewal_trap) && $renewal_trap) {
	header("Location: " . autoUrl("renewal/go"));
	exit();
}

$sql = $db->prepare("SELECT * FROM `users` WHERE `UserID` = ?");
$sql->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$row = $sql->fetch(PDO::FETCH_ASSOC);

$mobile = PhoneNumber::parse($row['Mobile']);

$contacts = new EmergencyContacts($db);
$contacts->byParent($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']);

$contactsArray = $contacts->getContacts();

$pagetitle = "My Emergency Contacts";

$url_path = "emergency-contacts";
include BASE_PATH . 'views/header.php';
if (isset($renewal_trap) && $renewal_trap) {
	include BASE_PATH . 'views/renewalTitleBar.php';
	$url_path = "renewal/emergencycontacts";
}

?>

<div class="container">
  <div class="row">
    <div class="col-lg-8">
      <h1>
        My Emergency Contacts
      </h1>
      <p class="lead">
        Add, edit or remove emergency contacts
      </p>
      <p class="border-bottom border-gray pb-2 mb-0">
        We'll use these emergency contacts for all swimmers connected to your account if we can't reach you on your
        phone number. You can change your phone number in <a href="<?=autoUrl("my-account")?>">My Account</a>
      </p>
      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AddNewSuccess'])) {
			echo $_SESSION['TENANT-' . app()->tenant->getId()]['AddNewSuccess'];
			unset($_SESSION['TENANT-' . app()->tenant->getId()]['AddNewSuccess']);
		} ?>
      <div class="mb-3">
        <div class="media pt-3">
          <div class="media-body pb-3 mb-0 lh-125 border-bottom border-gray">
            <div class="row align-items-center	">
              <div class="col-9">
                <p class="mb-0">
                  <strong>
                    <?=htmlspecialchars($row['Forename'] . " " . $row['Surname'])?>
                  </strong>
                  <em>(From My Account)</em>
                </p>
                <p class="mb-0">
                  <a href="<?=htmlspecialchars($mobile->format(PhoneNumberFormat::RFC3966))?>">
                    <?=htmlspecialchars($mobile->format(PhoneNumberFormat::NATIONAL))?>
                  </a>
                </p>
              </div>
              <?php if (!isset($renewal_trap) || !$renewal_trap) { ?>
              <div class="col text-sm-end">
                <a href="<?=autoUrl("my-account")?>" class="btn
							btn-primary">
                  Edit
                </a>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <?php for ($i = 0; $i < sizeof($contactsArray); $i++) {
			?>
        <div class="media pt-3">
          <div class="media-body pb-3 mb-0 lh-125 border-bottom border-gray">
            <div class="row align-items-center">
              <div class="col-9">
                <p class="mb-0">
                  <strong class="">
                    <?=htmlspecialchars($contactsArray[$i]->getName())?>
                  </strong>
                  <em>
                    (<?=htmlspecialchars($contactsArray[$i]->getRelation())?>)
                  </em>
                </p>
                <p class="mb-0">
                  <a href="tel:<?=htmlspecialchars($contactsArray[$i]->getRFCContactNumber())?>">
                    <?=htmlspecialchars($contactsArray[$i]->getNationalContactNumber())?>
                  </a>
                </p>
              </div>
              <div class="col text-sm-end">
                <a href="<?=autoUrl($url_path . "/edit/" . $contactsArray[$i]->getID())?>" class="btn btn-primary">
                  Edit
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php
		} ?>
      </div>
      <p>
        <a href="<?php echo autoUrl($url_path . "/new"); ?>" class="btn btn-success">
          Add New
        </a>
      </p>
      <p>
        Please let people know if you have assigned them as your emergency contacts.
      </p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();