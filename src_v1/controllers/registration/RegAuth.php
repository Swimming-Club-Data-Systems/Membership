<?php

$db = app()->db;

$rr = 0;

$getApplication = $db->prepare("SELECT * FROM `newUsers` WHERE `AuthCode` = ? AND `ID` = ? AND `Type` = 'Registration'");
$getApplication->execute([$token, $id]);

$row = $getApplication->fetch(PDO::FETCH_ASSOC);

$status = "nf";

if ($row == null) {
	halt(404);
}

$array = json_decode($row['UserJSON']);

//pre($array);

$username 			= $array->Username;
$forename	 			= $array->Forename;
$surname 				= $array->Surname;
$email 					= $array->EmailAddress;
$emailAuth 			= $array->EmailComms;
$mobile 				= $array->Mobile;
$smsAuth 				= $array->MobileComms;
$hashedPassword	= $array->Password;

$fam = false;
$fam_id = null;
/*
if (isset($array->FamilyIdentifier) || $array->FamilyIdentifier != null) {
	$fam = true;
	$fam_id = $array->FamilyIdentifier;
	if ($array->RequiresRegistraion) {
		//$rr = 1;
	}
}
*/

// Registration may be allowed
// Success
$addUser = $db->prepare("INSERT INTO `users`
(`UserID`, `Username`, `Password`, `EmailAddress`, `EmailComms`, `Forename`, `Surname`, `Mobile`, `MobileComms`, `RR`)
VALUES
(NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$addUser->execute([$username, $hashedPassword, $email, $emailAuth, $forename, $surname, $mobile, $smsAuth, $rr]);
$user_id = $db->lastInsertId();

try {
  $addPermission = $db->prepare("INSERT INTO `permissions` (`User`, `Permission`) VALUES (?, ?)");
  $addPermission->execute([
    $user_id,
    'Parent'
  ]);
} catch (PDOException $e) {

}

// Check it went in
$count = null;
if ($user_id != null) {
  $count = 1;
}

/*
if ($fam) {
	$sql = "UPDATE `members` INNER JOIN familyMembers ON members.MemberID = familyMembers.MemberID SET members.UserID = ? WHERE familyMembers.FamilyID = ?";

	try {
  	$query = $db->prepare($sql);
  	$query->execute([$user_id, $fam_id]);
  } catch (PDOException $e) {
  	halt(500);
  }
}
*/

if ($count == 1) {
	$_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationGoVerify'] = '
  <div class="alert alert-success mb-0">
    <p class="mb-0">
      <strong>
        Hello ' . htmlspecialchars($forename) . '! You have successfully verified your email address.
      </strong>
    </p>

    <p>
      You can now head to the login page to login using your email address and password.
    </p>

		<p class="mb-0">
			<a class="alert-link" href="' . autoUrl("") . '">
      	Login to your account
			</a>
    </p>
  </div>
  ';

  $deleteRow = $db->prepare("DELETE FROM `newUsers` WHERE `AuthCode` = ? AND `ID` = ?");
  $deleteRow->execute([$token, $id]);
} else if ($status == "nf") {
	$_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationGoVerify'] = '
	<div class="alert alert-warning mb-0">
    <p class="mb-0">
      <strong>
        We could not find your details.
      </strong>
    </p>

    <p>
      This error may occur if your email provider has already inspected this link. Try logging in to see if this is the case.
    </p>

		<p class="mb-0">
			<a class="alert-link" href="' . autoUrl("") . '">
      	Login to your account
			</a>
    </p>
  </div>
	';
}

header("Location: " . autoUrl("register"));
