<?php

$db = nezamy_app()->db;
$tenant = nezamy_app()->tenant;

$galaDetails = $db->prepare("SELECT GalaName `name`, GalaDate `ends`, CoachEnters, GalaFee fee, GalaFeeConstant gfc FROM galas WHERE GalaID = ? AND Tenant = ?");
$galaDetails->execute([
  $id,
  $tenant->getId()
]);
$gala = $galaDetails->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

if (!$gala['CoachEnters']) {
  halt(404);
}

$galaData = new GalaPrices($db, $id);

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$sessions = $getSessions->fetchAll(PDO::FETCH_ASSOC);

try {
$getAvailableSwimmers = $db->prepare("SELECT Member, MForename fn, MSurname sn, DateOfBirth dob, gs.`Name` gsname, members.ASANumber `se` FROM (((galaSessionsCanEnter ca INNER JOIN galaSessions gs ON gs.ID = ca.Session) INNER JOIN members ON ca.Member = members.MemberID) LEFT JOIN galaEntries ge ON ge.GalaID = gs.Gala AND ge.MemberID = members.MemberID) WHERE gs.Gala = ? AND ca.CanEnter = ? AND ge.EntryID IS NULL ORDER BY sn ASC, fn ASC");
$getAvailableSwimmers->execute([$id, true]);
$swimmers = $getAvailableSwimmers->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
} catch (Exception $e) {
  pre($e);
}

$now = new DateTime('now', new DateTimeZone('Europe/London'));
$eOY = new DateTime('last day of December ' . $now->format('Y'), new DateTimeZone('Europe/London'));

$fluidContainer = true;
$pagetitle = "Select entries for " . htmlspecialchars($gala['name']);
include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-8">
      <h1>Select entries for <?=htmlspecialchars($gala['name'])?></h1>
      <p class="lead">Below are all members which have indicated they are available at at least one session.</p>

      <?php if (isset($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['SuccessStatus']) && $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['SuccessStatus']) { ?>
      <div class="alert alert-success">
        <p class="mb-0"><strong>Entries completed successfully</strong></p>
        <p class="mb-0">Parents will be notified about their entries by email.</p>
      </div>
      <?php unset($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['SuccessStatus']); } ?>

      <?php if (isset($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['ErrorStatus']) && $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['ErrorStatus']) { ?>
      <div class="alert alert-success">
        <p class="mb-0"><strong>An error occurred</strong></p>
        <p class="mb-0">We've rolled back all changes.</p>
      </div>
      <?php unset($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['ErrorStatus']); } ?>

      <p>Once you have entered a swimmer for a gala they will not appear in this list and you will have to edit or delete an individual entry.</p>
      <p>Please remember to check which sessions events are in.</p>
      <p>Options are available to control whether;</p>
      <ul>
        <li>parents can edit entries</li>
        <li>parents can veto entries</li>
      </ul>
      <p>When you submit this form, parents will be sent an email detailing their entries.</p>
    </div>
  </div>

  <form method="post">

    <?php if ($swimmers == null) { ?>
    <div class="alert alert-warning">
      <p class="mb-0"><strong>No swimmers have declared they are available for this gala.</strong></p>
      <p class="mb-0">Please try again later. You may need to tell parents that they must indicate availability.</p>
    </div>
    <?php } else { $c = 0; ?>
    <div class="list-group mb-3">
      <?php foreach ($swimmers as $member => $info) { ?>

      <?php
      // Date of birth data
      $dob = new DateTime($info[0]['dob'], new DateTimeZone('Europe/London'));
      $ageOnLastDay = $now->diff($dob);
      $ageAtEOY = $eOY->diff($dob);
      ?>
      <div class="list-group-item <?php if ($c%2 == 1) { ?>bg-light<?php } ?>">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h2>
              <?=htmlspecialchars($info[0]['sn'] . ', ' . $info[0]['fn'])?>
            </h2>
            <p class="mb-0">
              <strong>
                <?=htmlspecialchars($info[0]['fn'])?> has indicated they can enter;
              </strong>
            </p>
            <ul class="list-unstyled mb-0">
              <?php foreach ($info as $row) { ?>
              <li><?=htmlspecialchars($row['gsname'])?></li>
              <?php } ?>
            </ul>
            <div class="d-block d-md-none mb-3"></div>
          </div>
          <div class="col-md-6 text-md-right">
            <ul class="list-unstyled mb-0">
              <li><strong>Date of birth:</strong> <?=$dob->format('j F Y')?></li>
              <li><strong>Age on day:</strong> <?=$ageOnLastDay->format('%y')?></li>
              <li><strong>Age at end of year:</strong> <?=$ageAtEOY->format('%y')?></li>
              <li><strong>Swim England number:</strong> <span class="mono"><?=htmlspecialchars($info[0]['se'])?></span></li>
            </ul>
          </div>
        </div>

        <hr>

        <div class="row mb-3">
          <?php if ($galaData->getEvent('50Free')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-50Free" name="<?=$member?>-50Free">
            <label class="custom-control-label" for="<?=$member?>-50Free">50 Freestyle</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('100Free')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-100Free" name="<?=$member?>-100Free">
            <label class="custom-control-label" for="<?=$member?>-100Free">100 Freestyle</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('200Free')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-200Free" name="<?=$member?>-200Free">
            <label class="custom-control-label" for="<?=$member?>-200Free">200 Freestyle</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('400Free')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-400Free" name="<?=$member?>-400Free">
            <label class="custom-control-label" for="<?=$member?>-400Free">400 Freestyle</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('800Free')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-800Free" name="<?=$member?>-800Free">
            <label class="custom-control-label" for="<?=$member?>-800Free">800 Freestyle</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('1500Free')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-1500Free" name="<?=$member?>-1500Free">
            <label class="custom-control-label" for="<?=$member?>-1500Free">1500 Freestyle</label> 
          </div>
          </div>
          <?php } ?>
        </div>
        <div class="row mb-3">
          <?php if ($galaData->getEvent('50Breast')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-50Breast" name="<?=$member?>-50Breast">
            <label class="custom-control-label" for="<?=$member?>-50Breast">50 Breaststroke</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('100Breast')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-100Breast" name="<?=$member?>-100Breast">
            <label class="custom-control-label" for="<?=$member?>-100Breast">100 Breaststroke</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('200Breast')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-200Breast" name="<?=$member?>-200Breast">
            <label class="custom-control-label" for="<?=$member?>-200Breast">200 Breaststroke</label>
          </div>
          </div>
          <?php } ?>
        </div>
        <div class="row mb-3">
          <?php if ($galaData->getEvent('50Fly')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-50Fly" name="<?=$member?>-50Fly">
            <label class="custom-control-label" for="<?=$member?>-50Fly">50 Butterfly</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('100Fly')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-100Fly" name="<?=$member?>-100Fly">
            <label class="custom-control-label" for="<?=$member?>-100Fly">100 Butterfly</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('200Fly')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-200Fly" name="<?=$member?>-200Fly">
            <label class="custom-control-label" for="<?=$member?>-200Fly">200 Butterfly</label>
          </div>
          </div>
          <?php } ?>
        </div>
        <div class="row mb-3">
          <?php if ($galaData->getEvent('50Back')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-50Back" name="<?=$member?>-50Back">
            <label class="custom-control-label" for="<?=$member?>-50Back">50 Backstroke</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('100Back')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-100Back" name="<?=$member?>-100Back">
            <label class="custom-control-label" for="<?=$member?>-100Back">100 Backstroke</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('200Back')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-200Back" name="<?=$member?>-200Back">
            <label class="custom-control-label" for="<?=$member?>-200Back">200 Backstroke</label>
          </div>
          </div>
          <?php } ?>
        </div>
        <div class="row">
          <?php if ($galaData->getEvent('100IM')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-100IM" name="<?=$member?>-100IM">
            <label class="custom-control-label" for="<?=$member?>-100IM">100 IM</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('150IM')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-150IM" name="<?=$member?>-150IM">
            <label class="custom-control-label" for="<?=$member?>-150IM">150 IM</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('200IM')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-200IM" name="<?=$member?>-200IM">
            <label class="custom-control-label" for="<?=$member?>-200IM">200 IM</label>
          </div>
          </div>
          <?php } ?>
          <?php if ($galaData->getEvent('400IM')->isEnabled()) { ?>
          <div class="col-sm-4 col-md-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" value="1" class="custom-control-input" id="<?=$member?>-400IM" name="<?=$member?>-400IM">
            <label class="custom-control-label" for="<?=$member?>-400IM">400 IM</label>
          </div>
          </div>
          <?php } ?>
        </div>
      </div>
      <?php $c++; } ?>
    </div>

    <div class="form-group">
      <div class="custom-control custom-checkbox">
        <input type="checkbox" value="1" class="custom-control-input" id="parent-veto" name="parent-veto">
        <label class="custom-control-label" for="parent-veto">Allow parents to reject your entries and not enter the gala</label>
      </div>
    </div>

    <div class="form-group">
      <div class="custom-control custom-checkbox">
        <input type="checkbox" value="1" class="custom-control-input" id="lock-entry" name="lock-entry">
        <label class="custom-control-label" for="lock-entry">Prevent parents from editing your entries</label>
      </div>
    </div>

    <p>
      <button class="btn btn-success" type="submit">
        Submit entries
      </button>
    </p>

    <p>
      Parents will be sent an email detailing all entries made by you and the fees payable for each entry.
    </p>

    <?php } ?>

  </form>
</div>

<?php 

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();