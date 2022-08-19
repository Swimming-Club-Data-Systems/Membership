<?php

$db = app()->db;
$tenant = app()->tenant;

$getClasses = $db->prepare("SELECT `ID`, `Name`, `Description` FROM `clubMembershipClasses` WHERE `Tenant` = ? AND `Type` = ? ORDER BY `Name` ASC");
$getClasses->execute([
  $tenant->getId(),
  'club'
]);
$class = $getClasses->fetch(PDO::FETCH_ASSOC);

$fluidContainer = true;

$pagetitle = "Membership Options";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/settings/SettingsLinkGroup.json'));
      echo $list->render('settings-fees');
      ?>
    </aside>
    <div class="col-md-9">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings')) ?>">Settings</a></li>
          <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('settings/fees')) ?>">Fees</a></li>
          <li class="breadcrumb-item active" aria-current="page">Memberships</li>
        </ol>
      </nav>

      <main>
        <h1>Membership Options</h1>
        <p class="lead">Set amounts for club, NGB and other membership fees</p>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']) { ?>
          <div class="alert alert-success">Changes saved successfully</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success']);
        } ?>

        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']) { ?>
          <div class="alert alert-danger">Changes could not be saved</div>
        <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error']);
        } ?>

        <p>
          Welcome to the new Club Membership Fee settings page. We now support multiple types of annual membership fee.
        </p>

        <p>
          This allows clubs to charge different members, such as masters or club volunteers at a different rate to other members.
        </p>

        <form method="post">

          <h2>Club Membership Classes</h2>

          <?php if ($class) { ?>

            <div class="list-group mb-3">
              <?php do { ?>
                <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/' . $class['ID'])) ?>" class="list-group-item list-group-item-action">
                  <p class="mb-0"><strong><?= htmlspecialchars($class['Name']) ?></strong></p>
                </a>
              <?php } while ($class = $getClasses->fetch(PDO::FETCH_ASSOC)); ?>
            </div>

            <p>
              <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/new?type=club')) ?>" class="btn btn-success">
                Add new
              </a>
            </p>

            <?php $getClasses->execute(); ?>
            <?php $class = $getClasses->fetch(PDO::FETCH_ASSOC); ?>
            <div class="mb-3">
              <label class="form-label" for="default-class">Select default club membership class</label>
              <select class="form-select" id="default-class" name="default-class">
                <option selected disabled>Open this select menu</option>
                <?php do { ?>
                  <option value="<?= htmlspecialchars($class['ID']) ?>" <?php if ($tenant->getKey('DEFAULT_MEMBERSHIP_CLASS') == $class['ID']) { ?>selected<?php } ?>><?= htmlspecialchars($class['Name']) ?></option>
                <?php } while ($class = $getClasses->fetch(PDO::FETCH_ASSOC)); ?>
              </select>
            </div>

            <?= \SCDS\CSRF::write(); ?>

            <p>
              <button type="submit" class="btn btn-success">Save default</button>
            </p>

          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>There are no club membership fee classes available</strong>
              </p>
            </div>

            <p>
              <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/new?type=club')) ?>" class="btn btn-success">
                Add new
              </a>
            </p>
          <?php } ?>

          <h2><?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> Membership Classes</h2>

          <?php

          $getClasses->execute([
            $tenant->getId(),
            'national_governing_body'
          ]);
          $class = $getClasses->fetch(PDO::FETCH_ASSOC);

          ?>

          <?php if ($class) { ?>

            <div class="list-group mb-3">
              <?php do { ?>
                <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/' . $class['ID'])) ?>" class="list-group-item list-group-item-action">
                  <p class="mb-0"><strong><?= htmlspecialchars($class['Name']) ?></strong></p>
                </a>
              <?php } while ($class = $getClasses->fetch(PDO::FETCH_ASSOC)); ?>
            </div>

            <p>
              <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/new?type=national_governing_body')) ?>" class="btn btn-success">
                Add new
              </a>
            </p>

            <?php $getClasses->execute(); ?>
            <?php $class = $getClasses->fetch(PDO::FETCH_ASSOC); ?>
            <div class="mb-3">
              <label class="form-label" for="default-ngb-class">Select default <?= htmlspecialchars($tenant->getKey('NGB_NAME')) ?> membership class</label>
              <select class="form-select" id="default-ngb-class" name="default-ngb-class">
                <option selected disabled>Select a default</option>
                <?php do { ?>
                  <option value="<?= htmlspecialchars($class['ID']) ?>" <?php if ($tenant->getKey('DEFAULT_NGB_MEMBERSHIP_CLASS') == $class['ID']) { ?>selected<?php } ?>><?= htmlspecialchars($class['Name']) ?></option>
                <?php } while ($class = $getClasses->fetch(PDO::FETCH_ASSOC)); ?>
              </select>
            </div>

            <?= \SCDS\CSRF::write(); ?>

            <p>
              <button type="submit" class="btn btn-success">Save default</button>
            </p>

          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>There are no national governing body membership fee classes available</strong>
              </p>
            </div>

            <p>
              <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/new?type=national_governing_body')) ?>" class="btn btn-success">
                Add new
              </a>
            </p>
          <?php } ?>

          <h2>Other (Arbitrary) Membership Classes</h2>

          <?php

          $getClasses->execute([
            $tenant->getId(),
            'other'
          ]);
          $class = $getClasses->fetch(PDO::FETCH_ASSOC);

          ?>

          <?php if ($class) { ?>

            <div class="list-group mb-3">
              <?php do { ?>
                <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/' . $class['ID'])) ?>" class="list-group-item list-group-item-action">
                  <p class="mb-0"><strong><?= htmlspecialchars($class['Name']) ?></strong></p>
                </a>
              <?php } while ($class = $getClasses->fetch(PDO::FETCH_ASSOC)); ?>
            </div>

            <p>
              <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/new?type=other')) ?>" class="btn btn-success">
                Add new
              </a>
            </p>

          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>There are no other membership fee classes available</strong>
              </p>
            </div>

            <p>
              <a href="<?= htmlspecialchars(autoUrl('settings/fees/membership-fees/new?type=other')) ?>" class="btn btn-success">
                Add new
              </a>
            </p>
          <?php } ?>

        </form>

      </main>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
