<?php

$member = $times = $error = null;

if (isset($_GET['member-id'])) {
  try {
    $client = new \RankingsDB\RankingsClient(app()->tenant->getKey('GLOBAL_PERSONAL_KEY'), (int) app()->tenant->getKey('GLOBAL_PERSONAL_KEY_ID_NUMBER'));
    $member = $client->getMemberDetails((int) $_GET['member-id']);

    $options = new \RankingsDB\GetTimesBuilder($member->MemberID());
    $times = $client->getTimes($options);
  } catch (\RankingsDb\exceptions\ConnectionException $e) {
    $error = 'Unable to connect to the rankings.';
  } catch (\RankingsDb\exceptions\MemberNotFound $e) {
    $error = 'We could not find a member with ' . app()->tenant->getKey('NGB_NAME') . ' number ' . trim($_GET['member-id']) . '.';
  } catch (\RankingsDb\exceptions\InvalidPersonalKey $e) {
    $error = 'The personal key provided is invalid.';
  } catch (Exception $e) {
    // Problem
    $error = 'An unknown problem occurred.';
  }
}

$pagetitle = "Member Lookup";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Lookup</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Member lookup</h1>
      <p class="lead">Search for an SE Member.</p>

      <?php if (app()->tenant->getKey('GLOBAL_PERSONAL_KEY') && (int) app()->tenant->getKey('GLOBAL_PERSONAL_KEY_ID_NUMBER')) { ?>

        <form method="get">

          <div class="mb-3">
            <label class="form-label" for="member-id">Membership Number</label>
            <input type="number" name="member-id" id="member-id" class="form-control" min="0" required <?php if (isset($member)) { ?>value="<?= htmlspecialchars($member->MemberID()) ?>" <?php } ?>>
            <div class="invalid-feedback">
              Please enter a valid number
            </div>
          </div>

          <p>
            <button type="submit" class="btn btn-success">
              Lookup
            </button>
          </p>
        </form>

        <?php if ($error) { ?>
          <div class="alert alert-danger">
            <p class="mb-0">
              <strong>Error</strong>
            </p>
            <p class="mb-0">
              <?= htmlspecialchars($error) ?>
            </p>
          </div>
        <?php } ?>

        <?php if ($member) { ?>

          <h2>Member record</h2>
          <dl class="row">
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->FirstName() . ' ' . $member->LastName()) ?></dd>

            <dt class="col-sm-3">Known as</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->KnownAs()) ?></dd>

            <dt class="col-sm-3">Swim England Number</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->MemberID()) ?></dd>

            <dt class="col-sm-3">Date of birth</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->DateOfBirth()->format('d/m/Y')) ?></dd>

            <dt class="col-sm-3">Age</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->Age()) ?></dd>

            <dt class="col-sm-3">Age at end of year</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->Age(new DateTime('last day of December this year', new DateTimeZone('Europe/London')))) ?></dd>

            <dt class="col-sm-3">Sex</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string) $member->Sex()) ?></dd>

            <dt class="col-sm-3">Country</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->CountryCode()) ?></dd>

            <!-- <dt class="col-sm-3">Lapsed</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->getLapsed()) ?></dd> -->

            <!-- <?php if ($member->SClass()) { ?>
              <dt class="col-sm-3">S Classification</dt>
              <dd class="col-sm-9">REF <?= htmlspecialchars($member->SClass()) ?></dd>
            <?php } ?> -->

            <!-- <?php if ($member->SMClass()) { ?>
              <dt class="col-sm-3">S (Medley) Classification</dt>
              <dd class="col-sm-9">REF <?= htmlspecialchars($member->SMClass()) ?></dd>
            <?php } ?> -->

            <!-- <?php if ($member->SBClass()) { ?>
              <dt class="col-sm-3">S (B) Classification</dt>
              <dd class="col-sm-9">REF <?= htmlspecialchars($member->SBClass()) ?></dd>
            <?php } ?> -->

            <!-- <dt class="col-sm-3">Membership Category</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($member->getIClass()) ?></dd> -->
          </dl>

        <?php } ?>

      <?php } else { ?>

        <div class="alert alert-info">
          <p class="mb-0">
            <strong>You must enter a membership number and personal key in system variables.</strong>
          </p>
          <p class="mb-0">
            <a href="<?= htmlspecialchars(autoUrl('settings/variables')) ?>" class="alert-link">Add personal key</a>
          </p>
        </div>

      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
