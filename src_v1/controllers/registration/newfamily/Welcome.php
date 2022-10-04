<?php

if (!user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
  halt(404);
}

$pagetitle = "Welcome to the Club";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/renewalTitleBar.php";

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>
        Welcome to <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>
      </h1>
      <p class="lead">
        We've saved your account details. We now need to run through some forms to complete the registration process. You will need your swimmers with you as you continue.
      </p>
      
      <p>We'll ask you to;</p>
      
      <ul>
        <li>Fill out medical forms for your swimmer(s),</li>
        <li>Provide us with emergency contact details,</li>
        <li>Agree to all relevent Codes of Conduct,</li>
        <li>Agree to the Terms and Conditions of Membership,</li>
        <li>Setup a Direct Debit for payments to <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> and finally,</li>
        <li>Pay your registration fees, which consist of Club and Swim England Fees.</li>
      </ul>

      <p>
        Failure to complete registration before the end of the calendar month in which you join the club may result in suspension of your swimmer(s).  This is because your Swim England Membership Fee covers insurance for your swimmers while taking part in the sport.
      </p>

      <p class="mb-0">
        <a href="<?php echo autoUrl("renewal/go"); ?>" class="btn btn-success">
          Start Registration
        </a>
      </p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

?>