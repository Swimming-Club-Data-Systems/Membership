<?php

$db = app()->db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash'], true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$pagetitle = "About AC";
$use_white_background = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>About AC</h1>
  <div class="row">
    <div class="col-sm-10 col-md-8">
      <form method="post" class="needs-validation" novalidate>
        <p class="lead">
          AC is the modern registration system for new members at <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>
        </p>

        <p>
          The AC registration process follows users all the way from requesting
          a trial to becoming paid up members. Users are guided along the way by
          our staff.
        </p>

        <p>
          Parents and swimmers now request trials through an online form and can
          then track their request as it is progressed by our teams. If they
          succeed in their trial, they will be offered a place in a squad via
          the AC system and invited to join the club, filling out all mandatory
          forms through AC in a GDPR compliant manner.
        </p>

        <p>
          The product name AC stands for Animated Carnival, the code name for
          this system when it was under construction. Technical documents and
          support articles may often refer to the Animated Carnival/AC name,
          despite it not being visible to most users.
        </p>

        <p>
          For further information about AC, contact <a
          href="mailto:support@chesterlestreetasc.co.uk">support@chesterlestreetasc.co.uk</a>.
        </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
