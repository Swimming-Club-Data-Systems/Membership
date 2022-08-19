<?php

$pagetitle = "Privacy Policy";

$db = app()->db;
$tenant = app()->tenant;


$privacy = app()->tenant->getKey('PrivacyPolicy');

$Extra = new ParsedownExtra();
$Extra->setSafeMode(true);
$search  = array("\n##### ", "\n#### ", "\n### ", "\n## ", "\n# ");
$replace = array("\n###### ", "\n######", "\n#### ", "\n### ", "\n## ");

$privacyPolicy = null;
if ($privacy != null && $privacy != "") {
  $privacyPolicy = $db->prepare("SELECT Content FROM posts WHERE ID = ? AND Tenant = ?");
  $privacyPolicy->execute([
    $privacy,
    $tenant->getId()
  ]);
  $privacyPolicy = str_replace($search, $replace, $privacyPolicy->fetchColumn());
  if ($privacyPolicy[0] == '#') {
    $privacyPolicy = '#' . $privacyPolicy;
  }
}

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>Privacy Policy</h1>
      <?php if ($privacyPolicy == null) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>Your club has no privacy policy set.</strong>
          </p>
          <p>
            Please speak to a member of club staff urgently about this issue.
          </p>
          <p class="mb-0">
            Clubs are not permitted by SCDS to use this software without a privacy policy in place.
          </p>
        </div>
        <p>
          In accordance with European Law, <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>, Swim England and British Swimming are Data Controllers for the purposes of the General Data Protection Regulation.
        </p>
        <p>
          By proceeding you agree to our Privacy Policy and the use of your data by <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>. Please note that you have also agreed to our use of you and your swimmer's data as part of your registration with the club and with British Swimming and Swim England (Formerly known as the ASA).
        </p>
        <p>
          We will be unable to provide this service for technical reasons if you do not consent to the use of this data.
        </p>
        <p>
          Contact a member of your committee if you have any questions or email <a href="mailto:<?= htmlspecialchars($tenant->getkey('CLUB_EMAIL')) ?>"><?= htmlspecialchars($tenant->getkey('CLUB_EMAIL')) ?></a>.
        </p>

        <p>
          <a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> or <a href="tel:+441912494320">call SCDS on +44 191 249 4320</a> for further help and support.
        </p>
      <?php } else { ?>
        <?= $Extra->text($privacyPolicy) ?>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
