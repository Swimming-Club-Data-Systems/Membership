<?php

$db = app()->db;
$tenant = app()->tenant;

$date = new DateTime('-9 years last day of December', new DateTimeZone('Europe/London'));
$now = new DateTime('now', new DateTimeZone('Europe/London'));

$findNGBCategory = $db->prepare("SELECT `ID` FROM `clubMembershipClasses` WHERE `Name` LIKE ? AND `Tenant` = ?");
$findNGBCategory->execute([
  '%1%',
  $tenant->getId(),
]);
$category = $findNGBCategory->fetchColumn();

$getMembers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn, DateOfBirth dob, NGBCategory cat FROM members WHERE members.Active AND members.Tenant = ? AND DateOfBirth <= ? AND NGBCategory = ? ORDER BY MForename ASC, MSurname ASC");
$getMembers->execute([
  $tenant->getId(),
  $date->format("Y-m-d"),
  $category
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

$getCategories = $db->prepare("SELECT `ID`, `Name` FROM `clubMembershipClasses` WHERE `Type` = ? AND `Tenant` = ? ORDER BY `Name` ASC");
$getCategories->execute([
  'national_governing_body',
  $tenant->getId(),
]);

$categories = $getCategories->fetchAll(PDO::FETCH_ASSOC);

$getSqauds = $db->prepare("SELECT SquadName FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE squadMembers.Member = ?");

$pagetitle = "Upgradeable Members";

$fluidContainer = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("members")) ?>">Members</a></li>
      <li class="breadcrumb-item active" aria-current="page">Upgradeable Members</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Upgradeable members</h1>
      <p class="lead">Upgradeable members are nine years old* by the end of the year and are Category 1 Swim England members.</p>

      <p>This report helps you identify which members will need to be upgraded to Category 2 membership for next year.</p>

      <?php if ($member) { ?>
        <form method="post">

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['CatChangesSaveError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['CatChangesSaveError']) { ?>
            <div class="alert alert-danger">
              <p class="mb-0">
                <strong>An error has occurred</strong>
              </p>
              <p class="mb-0">
                We were unable to save your Swim England Membership Category changes for these members.
              </p>
            </div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['CatChangesSaveError']);
          } ?>

          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['CatChangesSavedSuccessfully']) && $_SESSION['TENANT-' . app()->tenant->getId()]['CatChangesSavedSuccessfully']) { ?>
            <div class="alert alert-success">
              <p class="mb-0">
                <strong>Changes saved successfully</strong>
              </p>
              <p class="mb-0">
                Remember that members will not pay their new Swim England fee until their next registration/renewal.
              </p>
            </div>
          <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['CatChangesSavedSuccessfully']);
          } ?>

          <p>
            To change a member's Swim England category, select the new category from the dropdown menu. press <strong>Save Changes</strong> when you're finished.
          </p>

          <ul class="list-group mb-3">
            <?php do {
              $dob = new DateTime($member['dob'], new DateTimeZone('Europe/London'));
              $age = $dob->diff($now)->y;
              $getSqauds->execute([$member['id']]);
              $squads = $getSqauds->fetchColumn();
            ?>
              <li class="list-group-item list-group-item-action">
                <div class="row align-items-center">
                  <div class="col-md">
                    <a href="<?= htmlspecialchars(autoUrl("members/" . $member['id'])) ?>" class="">
                      <strong><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['fn'], $member['sn'])) ?></strong>
                    </a>
                    <?php if ($squads) { ?>
                      <ul class="list-unstyled">
                        <?php do { ?>
                          <li>
                            <?= htmlspecialchars($squads) ?>
                          </li>
                        <?php } while ($squads = $getSqauds->fetchColumn()); ?>
                      </ul>
                    <?php } ?>
                    <div class="mb-3 d-md-none"></div>
                  </div>
                  <div class="col-md">
                    <label class="form-label" for="<?= htmlspecialchars("se-cat-" . $member['id']) ?>" class="d-none">
                      Swim England Membership Category
                    </label>
                    <select class="form-select" id="<?= htmlspecialchars("se-cat-" . $member['id']) ?>" name="<?= htmlspecialchars("se-cat-" . $member['id']) ?>">
                      <option value="0" <?php if (!$member['cat']) { ?>selected<?php } ?>>Not an SE member</option>
                      <?php foreach ($categories as $cat) { ?>
                        <option value="<?= htmlspecialchars($cat['ID']) ?>" <?php if ($member['cat'] == $cat['ID']) { ?>selected<?php } ?>><?= htmlspecialchars($cat['Name']) ?></option>
                      <?php } ?>
                    </select>
                    <div class="mb-3 d-md-none"></div>
                  </div>
                  <div class="col-md text-md-end">
                    <?= htmlspecialchars($dob->format("j F Y")) ?> (<?= htmlspecialchars($age) ?>)
                  </div>
                </div>
              </li>
            <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
          </ul>

          <p>
            <button type="submit" class="btn btn-success">
              Save changes
            </button>
          </p>
        </form>
      <?php } else { ?>
        <div class="alert alert-info">
          <p class="mb-0"><strong>There are no upgradeable members at the moment.</strong></p>
          <p class="mb-0">Upgradeable members are nine years old by the end of the year and are Category 1 Swim England members.</p>
        </div>
      <?php } ?>

      <p>* Born on or before <?= htmlspecialchars($date->format("j F Y")) ?></p>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
