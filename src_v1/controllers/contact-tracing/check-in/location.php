<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

$getLocation = $db->prepare("SELECT `ID`, `Name`, `Address` FROM covidLocations WHERE `ID` = ? AND `Tenant` = ?");
$getLocation->execute([
  $id,
  $tenant->getId()
]);
$location = $getLocation->fetch(PDO::FETCH_ASSOC);

if (!$location) {
  halt(404);
}

$guests = $members = $squads = $userSquads = null;
$isUserMember = false;
if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {

  $user = app()->user;
  if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
    $userSquads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
    $userSquads->execute([
      $tenant->getId(),
    ]);
  } else {
    $userSquads = $db->prepare("SELECT SquadName, SquadID FROM squadReps INNER JOIN squads ON squadReps.Squad = squads.SquadID WHERE User = ? ORDER BY SquadFee DESC, SquadName ASC");
    $userSquads->execute([
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    ]);
  }

  $guests = $db->prepare("SELECT ID, GuestName, GuestPhone FROM covidVisitors WHERE Inputter = ?");
  $guests->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  ]);
  $members = $db->prepare("SELECT MForename fn, MSurname sn, MemberID `id` FROM members WHERE `UserID` = ? ORDER BY fn ASC, sn ASC");
  $members->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
  ]);

  // Check if a member name is equal to the user name
  $getEqualCount = $db->prepare('SELECT COUNT(*) FROM members WHERE UserID = ? AND MForename COLLATE utf8mb4_general_ci LIKE ? AND MSurname COLLATE utf8mb4_general_ci LIKE ?');
  $getEqualCount->execute([
    app()->user->getId(),
    app()->user->getForename(),
    app()->user->getSurname(),
  ]);
  $isUserMember = $getEqualCount->fetchColumn() > 0;

} else {
  $squads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC;");
  $squads->execute([
    $tenant->getId()
  ]);
}

$pagetitle = 'Check In to ' . htmlspecialchars($location['Name']) . ' - Contact Tracing';

$locationAddress = json_decode($location['Address']);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing')) ?>">Tracing</a></li>
        <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) { ?>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('contact-tracing/locations')) ?>">Locations</a></li>
        <?php } ?>
        <li class="breadcrumb-item active" aria-current="page">Check In</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Check in to <?= htmlspecialchars($location['Name']) ?>
        </h1>
        <p class="lead mb-0">
          <?= htmlspecialchars($locationAddress->streetAndNumber) ?>
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>An error occurred</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']['message']) ?>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingError']);
      } ?>

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) { ?>

        <?php

        if ($userSquads && $squad = $userSquads->fetch(PDO::FETCH_ASSOC)) {
          $useCard = true;
        ?>
          <div class="">
            <h2>
              Squad Check In
            </h2>

            <p class="lead">
              Are you the COVID Liason for this session?
            </p>

            <form action="<?= htmlspecialchars(autoUrl('contact-tracing/check-in/' . $id)) ?>" method="get">

              <div class="mb-3">
                <label class="form-label" for="squad-list">Select squad</label>
                <select class="form-select" id="squad-list" name="squad">
                  <?php do { ?>
                    <option value="<?= htmlspecialchars($squad['SquadID']) ?>"><?= htmlspecialchars($squad['SquadName']) ?></option>
                  <?php } while ($squad = $userSquads->fetch(PDO::FETCH_ASSOC)); ?>
                </select>
              </div>

              <p class="">
                <button type="submit" class="btn btn-success">
                  Go to register
                </button>
              </p>

            </form>
          </div>

          <div class="text-center p-3 rounded border mb-3">
            <span class="font-weight-bold d-block mb-1">Otherwise...</span>
            <span class="d-block mb-1">Complete an individual check in form</span>
            <i class="fa fa-arrow-down" aria-hidden="true"></i>
          </div>
        <?php } ?>

        <h2>
          Tell us who's here
        </h2>
        <p class="lead">
          You can easily add yourself, members and guests.
        </p>

        <form method="post" class="needs-validation" novalidate>

          <?php if ($isUserMember) { ?>
          <?php } ?>
          <div class="cell <?php if ($isUserMember) { ?>d-none<?php } ?>">
            <h3>Yourself</h3>
            <p>
              Let us know if you're here or just dropping off your members.
            </p>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="user" name="user" value="1">
              <label class="form-check-label" for="user"><?= htmlspecialchars(app()->user->getName()) ?></label>
            </div>
          </div>

          <!-- <p>
            If there's nobody else, just check in now. Othwerwise, add your guests.
          </p> -->

          <?php if ($member = $members->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class="cell">
              <h3>Members</h3>
              <?php do { ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="<?= htmlspecialchars('member-' . $member['id']) ?>" name="<?= htmlspecialchars('member-' . $member['id']) ?>" value="1">
                  <label class="form-check-label" for="<?= htmlspecialchars('member-' . $member['id']) ?>"><?= htmlspecialchars($member['fn'] . ' ' . $member['sn']) ?></label>
                </div>
              <?php } while ($member = $members->fetch(PDO::FETCH_ASSOC)); ?>
            </div>

          <?php } ?>

          <?php if ($guest = $guests->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class="cell">
              <h3>Previous guests</h3>
              <?php do { ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="<?= htmlspecialchars('guest-' . $guest['ID']) ?>" name="<?= htmlspecialchars('guest-' . $guest['ID']) ?>" value="1">
                  <label class="form-check-label" for="<?= htmlspecialchars('guest-' . $guest['ID']) ?>"><?= htmlspecialchars($guest['GuestName']) ?> <em><?= htmlspecialchars($guest['GuestPhone']) ?></em></label>
                </div>
              <?php } while ($guest = $guests->fetch(PDO::FETCH_ASSOC)); ?>
            </div>
          <?php } ?>

          <div class="cell">
            <h3>Guests</h3>

            <p>
              Press <strong>Add a guest</strong> to add as many other people as required.
            </p>

            <div id="guests-box" data-init="false"></div>

            <p>
              We'll assume phone numbers are UK (+44) numbers. If you're providing a non-UK phone number, please include the country code.
            </p>

            <p>
              <button class="btn btn-primary" id="add-guest" type="button">
                Add a guest
              </button>
            </p>

          </div>

          <?= SCDS\CSRF::write() ?>

          <p>
            All done? Double check what you've entered and press <strong>Check in</strong>
          </p>

          <p>
            <button type="submit" class="btn btn-success">
              Check in
            </button>
          </p>
        </form>

      <?php } else { ?>
        <h2>
          You're a guest
        </h2>

        <p>
          If you have an account, <a href="<?= htmlspecialchars(autoUrl('login?target=' . urlencode('contact-tracing/check-in/' . $id))) ?>">please sign in</a> so we can pre-fill your details
        </p>

        <form method="post" class="needs-validation" novalidate>

          <div class="">
            <!-- <h3>Guests</h3> -->

            <!-- <?php if ($squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
            <p>
              If you're a swimmer, diver or water polo player, we can fetch your details if you tell us your squad and date of birth.
            </p>

            <p>
              <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#member-collapse" aria-expanded="false" aria-controls="member-collapse">
                I'm a member <i class="fa fa-caret-down" aria-hidden="true"></i>
              </button>
            </p>
            <div class="collapse" id="member-collapse">
              <div class="cell">
                <div class="mb-3">
                  <label class="form-label" for="squad">Squad</label>
                  <select class="form-select" id="squad" name="squad">
                    <option selected>Select a squad</option>
                    <?php do { ?>
                      <option value="<?= htmlspecialchars($squad['SquadID']) ?>"><?= htmlspecialchars($squad['SquadName']) ?></option>
                    <?php } while ($squad = $squads->fetch(PDO::FETCH_ASSOC)); ?>
                  </select>
                </div>

                <div class="mb-3 mb-0">
                  <label class="form-label" for="date-of-birth">Date of birth</label>
                  <input type="date" name="date-of-birth" id="date-of-birth" class="form-control">
                </div>
              </div>
            </div>
            <?php } ?> -->

            <p>
              Please start with your own details, then add those of any others who are with you.
            </p>

            <div id="guests-box" data-init="true"></div>

            <p>
              <button class="btn btn-primary" id="add-guest" type="button">
                Add a guest
              </button>
            </p>
          </div>

          <?= SCDS\CSRF::write() ?>

          <p>
            All done? Double check what you've entered and press <strong>Check in</strong>
          </p>

          <p>
            <button type="submit" class="btn btn-success">
              Check-in
            </button>
          </p>
        </form>
      <?php } ?>
    </div>
    <div class="col d-none d-lg-block">
      <div class="position-sticky top-3">
        <div class="cell">
          <h2>
            What do I need to do?
          </h2>
          <p class="lead">
            Every time you visit a session run by <?= htmlspecialchars($tenant->getName()) ?>, you should check in to your current location.
          </p>

          <p>
            Only one member of a party needs to check in. If you're a member and have an account with us, you can tick to say which of your members are with you today.
          </p>

          <p>
            All users can also register the details of guests (who are not members of the club) who are attending with you.
          </p>
        </div>
      </div>

    </div>
  </div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->addJs('public/js/contact-tracing/check-in.js');
$footer->render();
