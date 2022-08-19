<?php

$fluidContainer = true;

$db = app()->db;
$tenant = app()->tenant;

$termsDocuments = $db->prepare("SELECT Title, ID FROM posts WHERE Tenant = ? AND `Type` = 'terms_conditions' ORDER BY Title ASC");
$termsDocuments->execute([
  $tenant->getId()
]);
$termsDocuments = $termsDocuments->fetchAll(PDO::FETCH_ASSOC);

$welcomeDocuments = $db->prepare("SELECT Title, ID FROM posts WHERE Tenant = ? AND `Type` = 'corporate_documentation' ORDER BY Title ASC");
$welcomeDocuments->execute([
  $tenant->getId()
]);
$welcomeDocuments = $welcomeDocuments->fetchAll(PDO::FETCH_ASSOC);


$terms = app()->tenant->getKey('TermsAndConditions');
$privacy = app()->tenant->getKey('PrivacyPolicy');
$welcome = app()->tenant->getKey('WelcomeLetter');

$Extra = new ParsedownExtra();
$Extra->setSafeMode(true);
$search  = array("\n##### ", "\n#### ", "\n### ", "\n## ", "\n# ");
$replace = array("\n###### ", "\n###### ", "\n##### ", "\n#### ", "\n### ");

$termsDocument = null;
if ($terms != null && $terms != "") {
  $termsDocument = $db->prepare("SELECT Content FROM posts WHERE ID = ?");
  $termsDocument->execute([$terms]);
  $termsDocument = str_replace($search, $replace, $termsDocument->fetchColumn());
  if ($termsDocument[0] == '#') {
    $termsDocument = '##' . $termsDocument;
  }
}

$pagetitle = "Terms and Privacy Options";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-terms');
      ?>
    </aside>
    <div class="col-md-9">
      <main>
        <h1>Terms and Privacy Settings</h1>
        <form method="post">
          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']) { ?>
            <div class="alert alert-success">All changes saved.</div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']);
          } ?>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']) { ?>
            <div class="alert alert-danger">Changes were not saved.</div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']);
          } ?>

          <div class="mb-3">
            <label class="form-label" for="TermsAndConditions">Terms and Conditions Document</label>
            <select class="form-select" id="TermsAndConditions" name="TermsAndConditions" aria-describedby="TermsAndConditionsBlock">
              <option <?php if ($terms == null) { ?>selected<?php } ?>>
                Select an option
              </option>
              <?php foreach ($termsDocuments as $termsPosts) { ?>
                <option value="<?= htmlspecialchars($termsPosts['ID']) ?>" <?php if ($terms == $termsPosts['ID']) { ?>selected<?php } ?>>
                  <?= htmlspecialchars($termsPosts['Title']) ?>
                </option>
              <?php } ?>
            </select>
            <small id="TermsAndConditionsBlock" class="form-text text-muted">
              You can create a terms and conditions document in the <strong>Posts</strong> section of this system and select it here. It will be used in various parts of this system, including when new members sign up and when members renew.
            </small>
          </div>

          <div class="mb-3">
            <label class="form-label" for="PrivacyPolicy">Privacy Policy Document</label>
            <select class="form-select" id="PrivacyPolicy" name="PrivacyPolicy" aria-describedby="PrivacyPolicyBlock">
              <option <?php if ($privacy == null) { ?>selected<?php } ?>>
                Select an option
              </option>
              <?php foreach ($termsDocuments as $termsPosts) { ?>
                <option value="<?= htmlspecialchars($termsPosts['ID']) ?>" <?php if ($privacy == $termsPosts['ID']) { ?>selected<?php } ?>>
                  <?= htmlspecialchars($termsPosts['Title']) ?>
                </option>
              <?php } ?>
            </select>
            <small id="PrivacyPolicyBlock" class="form-text text-muted">
              You can create a privacy policy document in the <strong>Posts</strong> section of this system with the type <strong>terms and conditions</strong> and select it here. It will be used in various parts of this system, including when new members sign up and when members renew.
            </small>
          </div>

          <div class="mb-3">
            <label class="form-label" for="WelcomeLetter">Welcome letter</label>
            <select class="form-select" id="WelcomeLetter" name="WelcomeLetter" aria-describedby="WelcomeLetterBlock">
              <option <?php if ($welcome == null) { ?>selected<?php } ?>>
                Select an option
              </option>
              <?php foreach ($welcomeDocuments as $welcomePosts) { ?>
                <option value="<?= htmlspecialchars($welcomePosts['ID']) ?>" <?php if ($welcome == $welcomePosts['ID']) { ?>selected<?php } ?>>
                  <?= htmlspecialchars($welcomePosts['Title']) ?>
                </option>
              <?php } ?>
            </select>
            <small id="WelcomeLetterBlock" class="form-text text-muted">
              You can create a welcome letter in the <strong>Posts</strong> section of this system with the type <strong>terms and conditions</strong> and select it here. It will be used in various parts of this system, including when new members sign up.
            </small>
          </div>

          <p>
            <button class="btn btn-success" type="submit">
              Save
            </button>
          </p>
        </form>
      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
