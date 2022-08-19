<?php

$fluidContainer = true;

$db = app()->db;
$tenant = app()->tenant;

$codesOfConduct = $db->prepare("SELECT Title, ID FROM posts WHERE Tenant = ? AND `Type` = 'conduct_code' ORDER BY Title ASC");
$codesOfConduct->execute([
  $tenant->getId()
]);

$parentCode = app()->tenant->getKey('ParentCodeOfConduct');

$Extra = new ParsedownExtra();
$Extra->setSafeMode(true);
$search  = array("\n##### ", "\n#### ", "\n### ", "\n## ", "\n# ");
$replace = array("\n###### ", "\n###### ", "\n##### ", "\n#### ", "\n### ");

$codeOfConduct = null;
if ($parentCode != null && $parentCode != "") {
  $codeOfConduct = $db->prepare("SELECT Content FROM posts WHERE ID = ? AND Tenant = ?");
  $codeOfConduct->execute([
    $parentCode,
    $tenant->getId()
  ]);
  $codeOfConduct = str_replace($search, $replace, $codeOfConduct->fetchColumn());
  if ($codeOfConduct[0] == '#') {
    $codeOfConduct = '##' . $codeOfConduct;
  }
}

$pagetitle = "Parent Code of Conduct Options";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
        echo $list->render('settings-codes-of-conduct');
      ?>
    </aside>
    <div class="col-md-9 col-lg-6 col-xl-6">
      <main>
        <h1>Parent Code of Conduct</h1>
        <form method="post">
          <h2>Choose a code of conduct</h2>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']) { ?>
          <div class="alert alert-success">Parent code of conduct saved.</div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED']); } ?>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']) && $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']) { ?>
          <div class="alert alert-danger">Changes were not saved.</div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR']); } ?>

          <div class="mb-3">
            <label class="form-label" for="CodeOfConduct">Squad Code of Conduct</label>
            <select class="form-select" id="CodeOfConduct" name="CodeOfConduct" aria-describedby="conductSelectHelpBlock">
              <option <?php if ($parentCode == $null) { ?>selected<?php } ?>>
                Select an option
              </option>
              <?php while ($codeDetails = $codesOfConduct->fetch(PDO::FETCH_ASSOC)) { ?>
              <option value="<?=htmlspecialchars($codeDetails['ID'])?>"
                <?php if ($parentCode == $codeDetails['ID']) { ?>selected<?php } ?>>
                <?=htmlspecialchars($codeDetails['Title'])?>
              </option>
              <?php } ?>
            </select>
            <small id="conductSelectHelpBlock" class="form-text text-muted">
              You can create a code of conduct in the <strong>Posts</strong> section of this system and select it here. It will be used in various parts of this system, including when new members sign up and when members
              renew.
            </small>
          </div>

          <p>
            <button class="btn btn-success" type="submit">
              Save
            </button>
          </p>
        </form>

        <?php if ($parentCode != null) { ?>
        <div>
          <h2>View the code of conduct</h2>
          <?=$Extra->text($codeOfConduct)?>
        </div>
        <?php } ?>
      </main>
    </div>
    <aside class="col">
        <div class="alert alert-info">You can set squad codes of conduct in the edit squad pages.</div>
      </aside>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();