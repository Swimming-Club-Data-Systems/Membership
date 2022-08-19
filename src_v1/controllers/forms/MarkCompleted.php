<?php

$db = app()->db;
$tenant = app()->tenant;

// Check member
$getMember = $db->prepare("SELECT COUNT(*) FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $member,
  $tenant->getId()
]);
if ($getMember->fetchColumn() == 0) {
  halt(404);
}

use Respect\Validation\Validator as v;

try {

  $jsonArray = [
    "Details" => $about
  ];
  $json = json_encode($json);

  $datetime;

  if (v::date()->validate($date)) {
    $datetime = new DateTime('now', new DateTimeZone('UTC'));
    $date = $datetime->format('Y-m-d');
  }

  $getName;

  if ($type == 'member') {
    $check = $db->prepare("SELECT COUNT(*) FROM completedForms WHERE Form = ? AND `Date` = ? AND Member = ?");
    $check->execute([$form, $date, $member]);
    if ($check->fetchColumn() == 0) {
      $insert = $db->prepare("INSERT INTO completedForms (Form, `Date`, Member, About) VALUES (?, ?, ?, ?)");
      $insert->execute([$form, $date, $member, $json]);

      $getName = $db->prepare("SELECT MForename `first`, MSurname `last` FROM members WHERE MemberID = ?");
      $getName->execute([$member]);
    } else {
      halt(404);
    }
  } else if ($type == 'user') {
    $check = $db->prepare("SELECT COUNT(*) FROM completedForms WHERE Form = ? AND `Date` = ? AND User = ?");
    $check->execute([$form, $date, $member]);
    if ($check->fetchColumn() == 0) {
      $insert = $db->prepare("INSERT INTO completedForms (Form, `Date`, User, About) VALUES (?, ?, ?, ?)");
      $insert->execute([$form, $date, $member, $json]);

      $getName = $db->prepare("SELECT Forename `first`, Surname `last` FROM users WHERE UserID = ?");
      $getName->execute([$member]);
    } else {
      halt(404);
    }
  } else {
    halt(404);
  }

  $name = $getName->fetch(PDO::FETCH_ASSOC);

  if ($name == null) {
    halt(404);
  }

  $pagetitle = "Form Processing";
  include BASE_PATH . 'views/header.php';

?>

  <div class="container-xl">
    <div class="row">
      <div class="col-md-8">
        <h1>Success</h1>
        <p class="lead">
          We've successfully marked <?= htmlspecialchars($name['first'] . ' ' . $name['last']) ?>'s <?= htmlspecialchars($form) ?> form as complete.
        </p>

        <p>
          The date on the form was <?= $datetime->format('l j F Y') ?>.
        </p>

        <p>
          This form cannot be marked as complete again.
        </p>

        <p>
          <a href="<?= autoUrl("") ?>" class="btn btn-success">
            Go to the homepage
          </a>
        </p>
      </div>
    </div>
  </div>

<?php

  $footer = new \SCDS\Footer();
  $footer->render();
} catch (Exception $e) {
  halt(404);
}
