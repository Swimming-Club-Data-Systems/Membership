<?php
$forenameUpdate = false;
$middlenameUpdate = false;
$surnameUpdate = false;
$dateOfBirthUpdate = false;
$sexUpdate = false;
$medicalNotesUpdate = false;
$otherNotesUpdate = false;
$update = false;
$successInformation = "";

$query = "SELECT * FROM members WHERE MemberID = '$idLast' ";
$result = mysqli_query($link, $query);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

$forename = $row['MForename'];
$middlename = $row['MMiddleNames'];
$surname = $row['MSurname'];
$dateOfBirth = $row['DateOfBirth'];
$sex = $row['Gender'];
$medicalNotes = $row['MedicalNotes'];
$otherNotes = $row['OtherNotes'];

// Get the swimmer name
$sqlSecurityCheck = "SELECT `MForename`, `MSurname`, `UserID` FROM `members` WHERE MemberID = '$idLast';";
$resultSecurityCheck = mysqli_query($link, $sqlSecurityCheck);
$swimmersSecurityCheck = mysqli_fetch_array($resultSecurityCheck, MYSQLI_ASSOC);

if ($swimmersSecurityCheck['UserID'] == $userID && $resultSecurityCheck) {
  if (!empty($_POST['forename'])) {
    $newForename = mysqli_real_escape_string($link, trim(htmlspecialchars(ucwords($_POST['forename']))));
    if ($newForename != $forename) {
      $sql = "UPDATE `members` SET `MForename` = '$newForename' WHERE `MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      $forenameUpdate = true;
      $update = true;
    }
  }
  if (isset($_POST['middlenames'])) {
    $newMiddlenames = mysqli_real_escape_string($link, trim(htmlspecialchars(ucwords($_POST['middlenames']))));
    if ($newMiddlenames != $middlename) {
      $sql = "UPDATE `members` SET `MMiddleNames` = '$newMiddlenames' WHERE `MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      $middlenameUpdate = true;
      $update = true;
    }
  }
  if (!empty($_POST['surname'])) {
    $newSurname = mysqli_real_escape_string($link, trim(htmlspecialchars(ucwords($_POST['surname']))));
    if ($newSurname != $surname) {
      $sql = "UPDATE `members` SET `MSurname` = '$newSurname' WHERE `MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      $surnameUpdate = true;
      $update = true;
    }
  }
  if (!empty($_POST['datebirth'])) {
    $newDateOfBirth = mysqli_real_escape_string($link, trim(htmlspecialchars(ucwords($_POST['datebirth']))));
    // NEEDS WORK FOR DATE TO BE RIGHT
    if ($newDateOfBirth != $dateOfBirth) {
      $sql = "UPDATE `members` SET `DateOfBirth` = '$newDateOfBirth' WHERE `MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      $dateOfBirthUpdate = true;
      $update = true;
    }
  }
  if (!empty($_POST['sex'])) {
    $newSex = mysqli_real_escape_string($link, trim(htmlspecialchars(ucwords($_POST['sex']))));
    if ($newSex != $sex) {
      $sql = "UPDATE `members` SET `Gender` = '$newSex' WHERE `MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      $sexUpdate = true;
      $update = true;
    }
  }
  if (isset($_POST['medicalNotes'])) {
    $newMedicalNotes = mysqli_real_escape_string($link, trim(htmlspecialchars(ucwords($_POST['medicalNotes']))));
    if ($newMedicalNotes != $medicalNotes) {
      $sql = "UPDATE `members` SET `MedicalNotes` = '$newMedicalNotes' WHERE `MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      $medicalNotesUpdate = true;
      $update = true;
    }
  }
  if (isset($_POST['otherNotes'])) {
    $newOtherNotes = mysqli_real_escape_string($link, trim(htmlspecialchars(ucwords($_POST['otherNotes']))));
    if ($newOtherNotes != $otherNotes) {
      $sql = "UPDATE `members` SET `OtherNotes` = '$newOtherNotes' WHERE `MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      $otherNotesUpdate = true;
      $update = true;
    }
  }
  if ((!empty($_POST['disconnect'])) && (!empty($_POST['disconnectKey']))) {
    $disconnect = mysqli_real_escape_string($link, trim(htmlspecialchars($_POST['disconnect'])));
    $disconnectKey = mysqli_real_escape_string($link, trim(htmlspecialchars($_POST['disconnectKey'])));
    if ($disconnect == $disconnectKey) {
      $newKey = generateRandomString(8);
      $sql = "UPDATE `members` SET `UserID` = NULL, `AccessKey` = '$newKey' WHERE `MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      header("Location: " . autoUrl("swimmers"));
    }
  }
  if (!empty($_POST['swimmerDeleteDanger'])) {
    $deleteKey = mysqli_real_escape_string($link, trim(htmlspecialchars($_POST['swimmerDeleteDanger'])));
    if ($deleteKey == $dbAccessKey) {
      $sql = "DELETE FROM `members` WHERE `members`.`MemberID` = '$idLast'";
      mysqli_query($link, $sql);
      header("Location: " . autoUrl("swimmers"));
    }
  }
}

$pagetitle;
if ($swimmersSecurityCheck['UserID'] == $userID && $resultSecurityCheck) {
  $pagetitle = "Swimmer: " . $swimmersSecurityCheck['MForename'] . " " . $swimmersSecurityCheck['MSurname'];
  $sqlSwim = "SELECT members.MForename, members.MForename, members.MMiddleNames, members.MSurname, users.EmailAddress, members.ASANumber, squads.SquadName, squads.SquadFee, squads.SquadCoach, squads.SquadTimetable, squads.SquadCoC, members.DateOfBirth, members.Gender, members.MedicalNotes, members.OtherNotes, members.AccessKey FROM ((members INNER JOIN users ON members.UserID = users.UserID) INNER JOIN squads ON members.SquadID = squads.SquadID) WHERE members.MemberID = '$idLast';";
  $resultSwim = mysqli_query($link, $sqlSwim);
  $rowSwim = mysqli_fetch_array($resultSwim, MYSQLI_ASSOC);
  $title = null;
  $content = '<div class="row align-items-center"><div class="col-sm-8"><h1>Editing ' . $swimmersSecurityCheck['MForename'] . ' ' . $swimmersSecurityCheck['MSurname'] . '</h1></div><div class="col-sm-4 text-right"><a class="btn btn-dark" href="../' . $idLast . '">Exit Edit Mode</a></div></div>';
  if ($update) {
  $content .= '<div class="alert alert-success">
    <strong>We have updated</strong>
    <ul class="mb-0">';
      if ($forenameUpdate) { $content .= '<li>Your first name</li>'; }
      if ($middlenameUpdate) { $content .= '<li>Your middle name(s)</li>'; }
      if ($surnameUpdate) { $content .= '<li>Your last address</li>'; }
      if ($dateOfBirthUpdate) { $content .= '<li>Your date of birth</li>'; }
      if ($sexUpdate) { $content .= '<li>Your sex</li>'; }
      if ($medicalNotesUpdate) { $content .= '<li>Your medical notes</li>'; }
      if ($otherNotesUpdate) { $content .= '<li>Your other notes</li>'; }
  $content .= '
    </ul>
  </div>';
  }
  // Main Info Content
  $content .= "<form method=\"post\">";
  $content .= "
  <div class=\"form-group\">
    <label for=\"forename\">Forename</label>
    <input type=\"text\" class=\"form-control\" id=\"forename\" name=\"forename\" placeholder=\"Enter a forename\" value=\"" . $rowSwim['MForename'] . "\" required>
  </div>";
  $content .= "
  <div class=\"form-group\">
    <label for=\"middlenames\">Middle Names</label>
    <input type=\"text\" class=\"form-control\" id=\"middlenames\" name=\"middlenames\" placeholder=\"Enter a middlename\" value=\"" . $rowSwim['MMiddleNames'] . "\">
  </div>";
  $content .= "
  <div class=\"form-group\">
    <label for=\"surname\">Surname</label>
    <input type=\"text\" class=\"form-control\" id=\"surname\" name=\"surname\" placeholder=\"Enter a surname\" value=\"" . $rowSwim['MSurname'] . "\" required>
  </div>";
  $content .= "
  <div class=\"form-group\">
    <label for=\"datebirth\">Date of Birth</label>
    <input type=\"date\" class=\"form-control\" id=\"datebirth\" name=\"datebirth\" pattern=\"[0-9]{4}-[0-9]{2}-[0-9]{2}\" placeholder=\"YYYY-MM-DD\" value=\"" . $rowSwim['DateOfBirth'] . "\" required>
  </div>";
  $content .= "
  <div class=\"form-group\">
    <label for=\"asaregnumber\">ASA Registration Number</label>
    <input type=\"test\" class=\"form-control\" id=\"asaregnumber\" name=\"asaregnumber\" placeholder=\"ASA Registration Numer\" value=\"" . $rowSwim['ASANumber'] . "\" readonly>
  </div>";
  /*$sql = "SELECT COLUMN_TYPE
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = 'chesterlestreetasc_co_uk_membership'
  AND TABLE_NAME = 'members'
  AND COLUMN_NAME = 'Gender';";
  $resultGender = mysqli_query($link, $sqlSwim);*/
  if ($rowSwim['Gender'] == "Male") {
    $content .= "
    <div class=\"form-group\">
      <label for=\"sex\">Sex</label>
      <select class=\"custom-select\" id=\"sex\" name=\"sex\" placeholder=\"Select\">
        <option value=\"Male\" selected>Male</option>
        <option value=\"Female\">Female</option>
      </select>
    </div>";
  }
  else {
    $content .= "
    <div class=\"form-group\">
      <label for=\"sex\">Sex</label>
      <select class=\"custom-select\" id=\"sex\" name=\"sex\" placeholder=\"Select\">
        <option value=\"Male\">Male</option>
        <option value=\"Female\" selected>Female</option>
      </select>
    </div>";
  }
  $content .= "
  <div class=\"form-group\">
    <label for=\"medicalNotes\">Medical Notes</label>
    <textarea class=\"form-control\" id=\"medicalNotes\" name=\"medicalNotes\" rows=\"3\" placeholder=\"Tell us about any medical issues\">" . $rowSwim['MedicalNotes'] . "</textarea>
  </div>";
  $content .= "
  <div class=\"form-group\">
    <label for=\"otherNotes\">Other Notes</label>
    <textarea class=\"form-control\" id=\"otherNotes\" name=\"otherNotes\" rows=\"3\" placeholder=\"Tell us any other notes for coaches\">" . $rowSwim['OtherNotes'] . "</textarea>
  </div>";
  $content .= "<button type=\"submit\" class=\"btn btn-success mb-3\">Update</button>";

  // Danger Zone at Bottom of Page
  $disconnectKey = generateRandomString(8);
  $content .= "
  <form method=\"post\">
    <div class=\"alert alert-danger\">
      <p><strong>Danger Zone</strong> <br>Actions here can be irreversible. Be careful what you do.</p>
      <div class=\"form-group\">
        <label for=\"disconnect\">Disconnect swimmer from your account with this Key \"" . $disconnectKey . "\"</label>
        <input type=\"text\" class=\"form-control\" id=\"disconnect\" name=\"disconnect\" aria-describedby=\"disconnectHelp\" placeholder=\"Enter the key\" onselectstart=\"return false\" onpaste=\"return false;\" onCopy=\"return false\" onCut=\"return false\" onDrag=\"return false\" onDrop=\"return false\" autocomplete=off>
        <small id=\"disconnectHelp\" class=\"form-text\">Enter the key in quotes above and press submit. This will dissassociate this swimmer from your account in all of our systems. You will need a new Access Key to add the swimmer again.</small>
      </div>
      <input type=\"hidden\" value=\"" . $disconnectKey . "\" name=\"disconnectKey\">
      <div class=\"form-group\">
        <label for=\"swimmerDeleteDanger\">Delete this Swimmer with this Key \"" . $rowSwim['AccessKey'] . "\"</label>
        <input type=\"text\" class=\"form-control\" id=\"swimmerDeleteDanger\" name=\"swimmerDeleteDanger\" aria-describedby=\"swimmerDeleteDangerHelp\" placeholder=\"Enter the key\" onselectstart=\"return false\" onpaste=\"return false;\" onCopy=\"return false\" onCut=\"return false\" onDrag=\"return false\" onDrop=\"return false\" autocomplete=off>
        <small id=\"swimmerDeleteDangerHelp\" class=\"form-text\">Enter the key in quotes above and press submit. This will delete this swimmer from all of our systems.</small>
      </div>
      <button type=\"submit\" class=\"btn btn-danger mb-3\">Delete or Disconnect</button>
    </div>
  </form>";
}
else {
  // Not allowed or not found
  $pagetitle = "Error 404 - Not found";
  $title = "Error 404 - Not found";
}

include "../header.php";
?>
<script src="<?php echo autoUrl('js/tinymce/tinymce.min.js') ?>" async defer></script>
<script>
  tinymce.init({
    selector: '#medicalNotes',
    branding: false,
  });
</script>
<?php

?>
