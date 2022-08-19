<?php

$tenant = app()->tenant;

use Respect\Validation\Validator as v;

// Assign form content to SESSION
$_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-FC'] = $_POST;

$isParent = false;
if ($_POST['is-parent']) {
  $isParent = true;
}

if (!v::email()->validate($_POST['email-addr'])) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['Email'] = "The email address is invalid";
  header("Location: " . autoUrl("services/request-a-trial"));
}

$dob = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];

if (!v::date()->validate($dob)) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['DOB'] = "The date of birth provided is not valid";
  header("Location: " . autoUrl("services/request-a-trial"));
}

if ($_POST['forename'] == "") {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['Parent-FN'] = "No parent first name";
  header("Location: " . autoUrl("services/request-a-trial"));
}

if ($_POST['surname'] == "") {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['Parent-LN'] = "No parent last name";
  header("Location: " . autoUrl("services/request-a-trial"));
}

if (!$isParent) {
  if ($_POST['swimmer-forename'] == "") {
    $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['Swimmer-FN'] = "No swimmer first name";
    header("Location: " . autoUrl("services/request-a-trial"));
  }

  if ($_POST['swimmer-surname'] == "") {
    $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['Swimmer-LN'] = "No swimmer last name";
    header("Location: " . autoUrl("services/request-a-trial"));
  }
}

if ($_POST['sex'] == "") {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['Swimmer-LN'] = "No swimmer sex provided";
  header("Location: " . autoUrl("services/request-a-trial"));
}

if ($_POST['experience'] == "") {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['Swimmer-Experience'] = "No experience option selected";
  header("Location: " . autoUrl("services/request-a-trial"));
}

/*
if (true) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors']['TESTING'] = "Testing system";
  header("Location: " . autoUrl("services/request-a-trial"));
}
*/

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Errors'])) {
  die();
}

$parent = $_POST['forename'] . ' ' . $_POST['surname'];

$swimmer = $_POST['swimmer-forename'] . ' ' . $_POST['swimmer-surname'];

if ($isParent) {
  $swimmer = $parent;
}

$asa = trim($_POST['swimmer-asa']);
$biog_link = 'https://www.swimmingresults.org/biogs/biogs_details.php?tiref=' . $asa;

$exp = "none";
if ($_POST['experience'] == 2) {
  $exp = "ducklings (pre stages)";
} else if ($_POST['experience'] == 3) {
  $exp = "school swimming lessons";
} else if ($_POST['experience'] == 4) {
  $exp = "ASA/Swim England Learn to Swim Stage 1-7";
} else if ($_POST['experience'] == 5) {
  $exp = "ASA/Swim England Learn to Swim Stage 8-10";
} else if ($_POST['experience'] == 6) {
  $exp = "swimming club";
}

$email_club = '
<p>' . $parent . ' has requested a trial for ' . $swimmer . '. To contact ' . $_POST['forename'] . ', email ' . $_POST['email-addr'] . ' or reply to this email. Here are the full request details.</p>';

if ($isParent) {
  $email_club = '
  <p>' . $parent . ' has requested a trial. To contact ' . $_POST['forename'] . ', email ' . $_POST['email-addr'] . ' or reply to this email. Here are the full request details.</p>';
}

$age = $age = date_diff(date_create($dob), date_create('today'))->y;

$email_club .= '
<h2>General Information</h2>
<p>' . $swimmer . '\'s date of birth is ' . date("j F Y", strtotime($dob)) . '. They are ' . $age . ' years old. They are ' . $_POST['sex'] . '</p>

<h2>Previous experience and achievements</h2>
<p>Their previous experience is ' . $exp . '.</p>';

if ($_POST['swimmer-xp'] != "") {
  $email_club .= '<p>' . $_POST['swimmer-xp'] . '</p>';
}

if ($_POST['swimmer-club'] != "" || $_POST['swimmer-asa'] != "") {
  $email_club .= '<h2>Previous Club(s)</h2><p>';
  if ($_POST['swimmer-club'] != "") {
    $email_club .= 'This swimmer swims or has swam at ' . $_POST['swimmer-club'];
  }
  if ($_POST['swimmer-club'] != "" && $_POST['swimmer-asa'] != "") {
    $email_club .= ' ';
  }
  if ($_POST['swimmer-asa'] != "") {
    $email_club .= 'Their Swim England Number is ' . $asa . '. <a href="' . $biog_link . '">View their biog</a>.';
  }
  $email_club .= '</p>';
}

if ($_POST['swimmer-med'] != "") {
  $email_club .= '<h2>Medical Information</h2><p>' . $_POST['swimmer-med'] . '</p>';
}

if ($_POST['questions'] != "") {
  $email_club .= '<h2>Questions and Notes</h2><p>' . $_POST['questions'] . '</p>';
}

$email_club .= '<h2>What Next></h2>
<p>Before replying, please wait a moment to check that this parent isn\'t applying for any more trials. Once you are sure of that, contact them by email to arrange a trial date. Head to ' . autoUrl("trials") . ' continue the trial process.</p>';

$hash = sha1($_POST['email-addr']);

$forText = 'for ' . $swimmer;
if ($isParent) {
  $forText = '';
}

$email_parent = '
<p>Hello ' . $parent . '</p>
<p>Thanks for your interest in a trial ' . $forText . ' at ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . '. We\'re working through your request and will get back to you as soon as we can.</p>';

$to_club = notifySend(null, 'New Trial Request', $email_club, 'Club Admins', htmlspecialchars(app()->tenant->getKey('CLUB_TRIAL_EMAIL')), ["Email" => "noreply@" . getenv('EMAIL_DOMAIN'), "Name" => app()->tenant->getKey('CLUB_NAME'), 'Reply-To' => $_POST['email-addr']]);

$to_parent = notifySend(null, 'Your Trial Request', $email_parent, $parent, $_POST['email-addr']);

if ($to_club && $to_parent) {
  $db = app()->db;

  $query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Tenant = ?");
  $query->execute([$hash]);
  if ($query->fetchColumn() == 0) {
    $query = $db->prepare("INSERT INTO joinParents (Hash, First, Last, Email, Tenant) VALUES (?, ?, ?, ?, ?)");
    $query->execute([
      $hash,
      htmlspecialchars(trim($_POST['forename'])),
      htmlspecialchars(trim($_POST['surname'])),
      trim($_POST['email-addr']),
      $tenant->getId()
    ]);
  }

  $swimmerForename = $_POST['swimmer-forename'];
  $swimmerSurname = $_POST['swimmer-surname'];
  if ($isParent) {
    $swimmerForename = $_POST['forename'];
    $swimmerSurname = $_POST['surname'];
  }

  $query = $db->prepare("INSERT INTO joinSwimmers (Parent, First, Last, DoB, XP, XPDetails, Medical, Questions, Club, ASA, Sex, Tenant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $query->execute([
    $hash,
    htmlspecialchars(trim($swimmerForename)),
    htmlspecialchars(trim($swimmerSurname)),
    $dob,
    $_POST['experience'],
    trim($_POST['swimmer-xp']),
    trim($_POST['swimmer-med']),
    trim($_POST['questions']),
    trim($_POST['swimmer-club']),
    trim($_POST['swimmer-asa']),
    trim($_POST['sex']),
    $tenant->getId()
  ]);

  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Success'] = true;
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-AddAnother'] = [
    'forename' => $_POST['forename'],
    'surname' => $_POST['surname'],
    'email-addr' => $_POST['email-addr']
  ];
} else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RequestTrial-Success'] = false;
}

header("Location: " . autoUrl("services/request-a-trial"));
